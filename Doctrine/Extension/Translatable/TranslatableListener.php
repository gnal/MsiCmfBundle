<?php

namespace Msi\CmfBundle\Doctrine\Extension\Translatable;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Common\EventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TranslatableListener implements EventSubscriber
{
    protected $container;
    protected $skipPostLoad = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::postLoad,
        );
    }

    public function postLoad(EventArgs $e)
    {
        $entity = $e->getEntity();

        if ($this->skipPostLoad === false && $entity instanceof TranslatableInterface) {
            $entity->setRequestLocale($this->container->get('request')->getLocale());
        }
    }

    public function setSkipPostLoad($skipPostLoad)
    {
        $this->skipPostLoad = $skipPostLoad;

        return $this;
    }
}
