<?php

namespace Msi\CmfBundle\Tools;

use Msi\CmfBundle\Doctrine\Extension\Uploadable\UploadableInterface;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

class Uploader
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }
    public function getUploadDir(UploadableInterface $entity)
    {
        return $this->kernel->getRootDir().'/../web/uploads/'.$entity->getUploadDir();
    }

    public function removeUpload(UploadableInterface $entity)
    {
        $finder = new Finder();

        // avoir error if dir doesnt exist
        if (!is_dir($this->getUploadDir($entity))) {
            return;
        }

        $finder->files()->in($this->getUploadDir($entity));

        foreach ($finder as $file) {
            if (is_file($file) && $file->getFilename() === $entity->getFilename()) {
                unlink($file);
            }
        }

        // if we upload in sub directories like with parent ID name, we delete these directories when they re empty
        if (preg_match('@/@', $entity->getUploadDir())) {
            @rmdir($this->getUploadDir($entity));
        }
    }

    public function preUpload(UploadableInterface $entity)
    {
        $file = $entity->getFile();

        if ($file === null) return;

        // if files isnt null it means we are uploading a new file therefore we have to remove old upload
        if ($entity->getId()) {
            $this->removeUpload($entity);
        }

        $entity->setFilename(uniqid(time()).'.'.$file->guessExtension());
    }

    public function postUpload(UploadableInterface $entity)
    {
        $file = $entity->getFile();

        if ($file === null) return;

        $file = $file->move($this->getUploadDir($entity), $entity->getFilename());

        $entity->processFile($file);

        unset($file);
    }
}
