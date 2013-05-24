<?php

namespace Msi\CmfBundle\Doctrine\Extension\Translatable\Traits;

trait TranslationEntity
{
    /**
     * @ORM\Column(type="string")
     */
    protected $locale;

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }
}
