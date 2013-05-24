<?php

namespace Msi\CmfBundle\Doctrine\Extension\Uploadable\Traits;

trait UploadableEntity
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $filename;

    protected $file;

    public function getPathname($prefix = '')
    {
        return '/uploads/'.$this->getUploadDir().'/'.$prefix.$this->filename;
    }

    public function processFile(\SplFileInfo $file)
    {
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
        $this->updatedAt = new \DateTime();

        return $this;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    abstract function getCreatedAt();

    abstract function getUpdatedAt();
}
