<?php

namespace Msi\CmfBundle\Doctrine\Extension\Timestampable;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Common\EventArgs;

class TimestampableListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
        );
    }

    public function prePersist(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($entity instanceof TimestampableInterface) {
            $entity->setCreatedAt(new \DateTime());
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    public function preUpdate(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($entity instanceof TimestampableInterface) {
            $entity->setUpdatedAt(new \DateTime());

            $em = $e->getEntityManager();
            $uow = $em->getUnitOfWork();
            $meta = $em->getClassMetadata(get_class($entity));
            $uow->recomputeSingleEntityChangeSet($meta, $entity);
        }
    }
}
