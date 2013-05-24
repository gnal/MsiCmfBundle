<?php

namespace Msi\CmfBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestListener
{
    private $provider;
    private $sc;

    public function __construct($provider, $sc)
    {
        $this->provider = $provider;
        $this->sc = $sc;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        if ($request->hasNamespace(['admin', 'login'])) {
            if (!is_object($this->sc->getToken()->getUser())) {
                return;
            }

            if ($this->sc->getToken()->getUser()->getLocale()) {
                $request->setLocale($this->sc->getToken()->getUser()->getLocale());
            } else if (!in_array($request->getLocale(), $this->provider->getSite()->getLocales())) {
                $request->setLocale($this->provider->getSite()->getLocale());
            }

            return;
        }

        if (!$this->provider->getSite()->getEnabled()) {
            die($this->provider->getSite()->getOfflineMessage() ?: 'offline');
        }

        if (!in_array($request->getLocale(), $this->provider->getSite()->getLocales())) {
            $request->setLocale($this->provider->getSite()->getLocale());
            // throw new NotFoundHttpException('"'.$request->getLocale().'" is not a valid locale.');
        }
    }
}
