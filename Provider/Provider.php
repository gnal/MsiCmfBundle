<?php

namespace Msi\CmfBundle\Provider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Provider
{
    protected $siteManager;
    protected $site;
    protected $request;

    public function __construct(Request $request, $siteManager)
    {
        $this->siteManager = $siteManager;
        $this->request = $request;
        $site = null;
    }

    public function getSite()
    {
        if (!$this->site) {
            $site = $this->siteManager->getFindByQueryBuilder(
                ['a.host' => $this->request->getHost()]
            )->getQuery()->getOneOrNullResult();

            if (!$site) {
                $site = $this->siteManager->getFindByQueryBuilder(
                    ['a.isDefault' => true]
                )->getQuery()->execute();
                if (!isset($site[0])) {
                    throw new NotFoundHttpException('No site was found for host: '.$this->request->getHost());
                }
                $site = $site[0];
            }

            $this->site = $site;
        }

        return $this->site;
    }
}
