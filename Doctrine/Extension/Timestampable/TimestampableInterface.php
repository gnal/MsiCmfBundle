<?php

namespace Msi\CmfBundle\Doctrine\Extension\Timestampable;

interface TimestampableInterface
{
    function getCreatedAt();

    function getUpdatedAt();
}
