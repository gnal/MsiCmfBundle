<?php

namespace Msi\CmfBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends ContainerAware
{
    public function dashboardAction(Request $request)
    {
        return $this->container->get('templating')->renderResponse('MsiCmfBundle:Default:dashboard.html.twig');
    }

    public function limitAction(Request $request)
    {
        $limit = intval($request->request->get('limit'));

        if ($limit < 1) {
            $limit = 25;
        }

        $this->container->get('session')->set('limit', $limit);

        if ($_SERVER['HTTP_REFERER']) {
            $url = preg_replace('@\??&?page=\d+@', '', $_SERVER['HTTP_REFERER']);
        } else {
            $url = '/';
        }

        return new RedirectResponse($url);
    }
}
