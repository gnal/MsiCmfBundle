<?php

namespace Msi\CmfBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterEntityResponseEvent extends EntityEvent
{
    private $response;

    public function __construct($entity, Request $request, Response $response)
    {
        parent::__construct($entity, $request);

        $this->response = $response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
