<?php

namespace Msi\CmfBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Finder\Finder;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Msi\CmfBundle\Event\FilterEntityResponseEvent;

class CoreController extends Controller
{
    protected $admin;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->admin = $this->get($this->get('request')->attributes->get('_admin'));
    }

    public function render($view, array $parameters = [], Response $response = null)
    {
        $parameters['admin'] = $this->admin;

        return $this->get('templating')->renderResponse($view, $parameters, $response);
    }

    public function listAction(Request $request)
    {
        $this->isGranted('read');

        $qb = $this->getIndexQueryBuilder($request, $this->admin);

        // Filters
        $parameters = [];
        $filterForm = $this->admin->getForm('filter');

        if (count($filterForm->all())) {
            $this->get('msi_cmf.filter.form.handler')->process($filterForm, $this->admin->getObject(), $qb);
            $parameters['filterForm'] = $filterForm->createView();
        }

        // Pager
        $pager = $this->get('msi_cmf.pager.factory')->create($qb, array('attr' => array('class' => 'pull-right')));
        $pager->paginate($request->query->get('page', 1), $this->get('session')->get('limit', 25));

        // Table
        $grid = $this->admin->getGrid();
        if (property_exists($this->admin->getObjectManager()->getClass(), 'position')) {
            $grid->setSortable(true);
        }

        $result = new ArrayCollection($pager->getIterator()->getArrayCopy());
        $this->admin->postLoad($result);
        $grid->setRows($result);

        $parameters['pager'] = $pager;

        return $this->render($this->admin->getOption('index_template'), $parameters);
    }

    public function newAction(Request $request)
    {
        $this->isGranted('create');
        $this->isGranted('ACL_CREATE', $this->admin->getObject());

        if ($this->processForm()) {

            // if sortable set position to 1 + last

            if (!$request->query->has('alt')) {
                return $this->getResponse();
            } else {
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('success!'));
                return new RedirectResponse($this->admin->genUrl('new'));
            }
        } else {
            if (in_array($request->getMethod(), array('POST', 'PUT'))) {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('error!'));
            }
        }

        return $this->render($this->admin->getOption('new_template'), ['form' => $this->admin->getForm()->createView()]);
    }

    public function editAction(Request $request)
    {
        if ($request->getMethod() === 'GET') {
            $this->isGranted('read');
            $this->isGranted('ACL_READ', $this->admin->getObject());
        } else {
            $this->isGranted('update');
            $this->isGranted('ACL_UPDATE', $this->admin->getObject());
        }

        if ($this->processForm()) {
            if (!$request->query->has('alt')) {
                $response = $this->getResponse();
            } else {
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('success!'));
                $response = $this->render($this->admin->getOption('edit_template'), ['form' => $this->admin->getForm()->createView()]);
            }

            $this->get('event_dispatcher')->dispatch('msi_cmf.entity.update.completed', new FilterEntityResponseEvent($this->admin->getObject(), $request, $response));

            return $response;
        } else {
            if (in_array($request->getMethod(), array('POST', 'PUT'))) {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('error!'));
            }
        }

        $response = $this->render($this->admin->getOption('edit_template'), ['form' => $this->admin->getForm()->createView()]);

        return $response;
    }

    public function deleteAction()
    {
        $this->isGranted('delete');
        $this->isGranted('ACL_DELETE', $this->admin->getObject());

        if (!property_exists($this->admin->getObject(), 'deletedAt')) {
            $this->admin->getObjectManager()->delete($this->admin->getObject());
        } else {
            $this->admin->getObjectManager()->update($this->admin->getObject()->setDeletedAt(new \DateTime()));
        }

        return $this->getResponse();
    }

    public function deleteUploadAction()
    {
        $this->isGranted('update');
        $this->isGranted('ACL_UPDATE', $this->admin->getObject());

        if ($this->getRequest()->query->get('locale')) {
            $entity = $this->admin->getObject()->getTranslation($this->getRequest()->query->get('locale'));
        } else {
            $entity = $this->admin->getObject();
        }

        $this->get('msi_cmf.uploader')->removeUpload($entity);
        $entity->setFilename(null);
        $this->admin->getObjectManager()->update($entity);

        return $this->getResponse();
    }

    public function toggleAction()
    {
        $this->isGranted('update');
        $this->isGranted('ACL_UPDATE', $this->admin->getObject());

        $this->admin->getObjectManager()->toggle($this->admin->getObject(), $this->getRequest());

        return $this->getResponse();
    }

    public function sortAction(Request $request)
    {
        $this->isGranted('update');
        $this->isGranted('ACL_UPDATE', $this->admin->getObject());

        $itemId = $request->query->get('current');
        $nextItemId = $request->query->get('next');
        $prevItemId = $request->query->get('prev');

        $rows = $this->admin->getObjectManager()->getFindByQueryBuilder([], [], ['a.position' => 'ASC'])->getQuery()->execute();
        $item = $this->admin->getObjectManager()->getFindByQueryBuilder(['a.id' => $itemId])->getQuery()->getSingleResult();

        $i = 1;
        foreach ($rows as $row) {
            if ($row->getId() == $itemId) continue;

            if (!$nextItemId && $row->getId() == $prevItemId) {
                $item->setPosition($i+1);
                $this->admin->getObjectManager()->update($item);
            } elseif ($row->getId() == $nextItemId) {
                $item->setPosition($i);
                $this->admin->getObjectManager()->update($item);
                $i++;
            }

            $row->setPosition($i);
            $this->admin->getObjectManager()->update($row);
            $i++;
        }

        return $this->getResponse();
    }

    protected function getIndexQueryBuilder()
    {
        $where = [];
        $join = [];
        $sort = $this->admin->getOption('order_by');

        // sortable
        if (property_exists($this->admin->getObject(), 'position')) {
            $sort = ['a.position' => 'ASC'];
        }

        // translations
        if (property_exists($this->admin->getObject(), 'translations')) {
            $join['a.translations'] = 't';
        }

        // nested set
        if ($this->admin->hasParent() && $this->get('request')->query->get('parentId')) {
            foreach ($this->admin->getObjectManager()->getMetadata()->associationMappings as $association) {
                if (in_array($association['type'], [8, 2]) && $association['targetEntity'] === $this->admin->getParent()->getObjectManager()->getClass()) {
                    $relation = $association;
                }
            }
            if ($relation['type'] === 8) {
                $join['a.'.$relation['fieldName']] = $relation['fieldName'];
                $where[$relation['fieldName'].'.id'] = $this->get('request')->query->get('parentId');
            } else {
                $where['a.'.$relation['fieldName']] = $this->get('request')->query->get('parentId');
            }
        }

        if (!$this->get('request')->query->get('q')) {
            $qb = $this->admin->getObjectManager()->getFindByQueryBuilder($where, $join, $sort);
        } else {
            $qb = $this->admin->getObjectManager()->getSearchQueryBuilder($this->get('request')->query->get('q'), $this->admin->getOption('search_fields'), $where, $join, $sort);
        }

        // soft delete
        if (property_exists($this->admin->getObject(), 'deletedAt')) {
            $qb->andWhere($qb->expr()->isNull('a.deletedAt'));
        }

        $this->admin->buildListQuery($qb);

        return $qb;
    }

    protected function processForm()
    {
        $form = $this->admin->getForm();
        $process = $this->get('msi_cmf.admin.form.handler')->setAdmin($this->admin)->process($form);

        return $process;
    }

    protected function getResponse($data = [])
    {
        if ($this->get('request')->isXmlHttpRequest()) {
            $defaultData = [
                'status' => 'ok',
            ];
            $data = array_merge($defaultData, $data);

            return new JsonResponse($data);
        } else {
            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('success'));

            return $this->redirect($this->admin->genUrl('list'));
        }
    }

    protected function isGranted($role, $object = null)
    {
        if ($object !== null) {
            if (!$this->get('security.context')->isGranted($role, $this->admin->getObject())) {
                throw new AccessDeniedException();
            }
        } else {
            if (!$this->admin->isGranted($role)) {
                throw new AccessDeniedException();
            }
        }
    }
}
