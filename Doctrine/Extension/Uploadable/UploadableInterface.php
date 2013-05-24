<?php

namespace Msi\CmfBundle\Doctrine\Extension\Uploadable;

interface UploadableInterface
{
    function getUploadDir();

    function getPathname($prefix);

    function processFile(\SplFileInfo $file);

    function getFile();

    function setFile($file);

    function getFilename();

    function setFilename($filename);
}
