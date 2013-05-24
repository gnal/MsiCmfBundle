<?php

namespace Msi\CmfBundle\Request;

use Symfony\Component\HttpFoundation\Request as BaseRequest;

class Request extends BaseRequest
{
    // check with OR logic if pathInfo starts with one of the given namespaces
    public function hasNamespace($namespaces)
    {
        if (!is_array($namespaces)) {
            $namespaces = [$namespaces];
        }

        foreach ($namespaces as $namespace) {
            if (preg_match('#^(/[a-z]{2})?/'.$namespace.'#', $this->getPathInfo())) {
                return true;
            }
        }

        return false;
    }
}
