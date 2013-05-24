<?php

namespace Msi\CmfBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class EntityEvent extends Event
{
    private $request;
    private $entity;

    public function __construct($entity, Request $request)
    {
        $this->entity = $entity;
        $this->request = $request;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
