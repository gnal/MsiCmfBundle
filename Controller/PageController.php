<?php

namespace Msi\CmfBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

class PageController extends ContainerAware
{
    public function showAction(Request $request)
    {
        $criteria = array(
            't.published' => true,
            'a.site' => $this->container->get('msi_cmf.provider')->getSite(),
        );
        if ($request->attributes->get('slug')) {
            $criteria['t.slug'] = $request->attributes->get('slug');
        } else {
            $criteria['a.home'] = true;
        }

        $qb = $this->container->get('msi_cmf.page_manager')->getFindByQueryBuilder(
            $criteria,
            array('a.translations' => 't', 'a.blocks' => 'b'),
            array('b.position' => 'ASC')
        );
        $qb->andWhere($qb->expr()->isNull('a.route'));
        $pages = $qb->getQuery()->execute();

        if (!isset($pages[0])) {
            throw new NotFoundHttpException();
        }
        $page = $pages[0];

        return $this->container->get('templating')->renderResponse($page->getTemplate(), array('page' => $page));
    }
}
