<?php

namespace Msi\CmfBundle\Pager;

class PagerFactory
{
    public function create($query, array $options = array())
    {
        return new Pager($query, $options);
    }
}
