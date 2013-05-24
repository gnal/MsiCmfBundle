<?php

namespace Msi\CmfBundle\Doctrine\Extension\Uploadable;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Common\EventArgs;

use Msi\CmfBundle\Doctrine\Extension\Uploadable\UploadableInterface;

class UploadableListener implements EventSubscriber
{
    private $uploader;

    public function __construct($uploader)
    {
        $this->uploader = $uploader;
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        );
    }

    public function prePersist(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($entity instanceof UploadableInterface) {
            $this->uploader->preUpload($entity);
        }
    }

    public function preUpdate(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($entity instanceof UploadableInterface) {
            $this->uploader->preUpload($entity);
            $em   = $e->getEntityManager();
            $uow  = $em->getUnitOfWork();
            $meta = $em->getClassMetadata(get_class($entity));
            $uow->recomputeSingleEntityChangeSet($meta, $entity);
        }
    }

    public function postPersist(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($entity instanceof UploadableInterface) {
            $this->uploader->postUpload($entity);
        }
    }

    public function postUpdate(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($entity instanceof UploadableInterface) {
            $this->uploader->postUpload($entity);
        }
    }

    public function postRemove(EventArgs $e)
    {
        $entity = $e->getEntity();
        if ($entity instanceof UploadableInterface) {
            $this->uploader->removeUpload($entity);
        }
    }
}
