<?php

namespace Msi\CmfBundle\Controller\Admin;

use Msi\CmfBundle\Controller\CoreController;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\QueryBuilder;

class BlockController extends CoreController
{
    public function newAction(Request $request)
    {
        $this->isGranted('create');
        $this->isGranted('ACL_CREATE', $this->admin->getObject());

        if ($this->processForm()) {
            return new RedirectResponse($this->admin->genUrl('edit', array('id' => $this->admin->getObject()->getId())));
        } else {
            if (in_array($request->getMethod(), array('POST', 'PUT'))) {
                $this->container->get('session')->getFlashBag()->add('error', $this->container->get('translator')->trans('error!'));
            }
        }

        return $this->render('MsiCmfBundle:Admin:new.html.twig', array('form' => $this->admin->getForm()->createView()));
    }
}
