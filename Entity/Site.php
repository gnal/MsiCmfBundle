<?php

namespace Msi\CmfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Msi\CmfBundle\Doctrine\Extension\Translatable\TranslatableInterface;

/**
 * @ORM\MappedSuperclass
 */
abstract class Site implements TranslatableInterface
{
    use \Msi\CmfBundle\Doctrine\Extension\Translatable\Traits\TranslatableEntity;

    /**
     * @ORM\Column(type="string")
     */
    protected $host;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isDefault;

    /**
     * @ORM\Column(type="string")
     */
    protected $locale;

    /**
     * @ORM\Column(type="array")
     */
    protected $locales;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $offlineMessage;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->isDefault = false;
    }

    public function getIsDefault()
    {
        return $this->isDefault;
    }

    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getOfflineMessage()
    {
        return $this->offlineMessage;
    }

    public function setOfflineMessage($offlineMessage)
    {
        $this->offlineMessage = $offlineMessage;

        return $this;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocales()
    {
        return $this->locales;
    }

    public function setLocales($locales)
    {
        $this->locales = $locales;

        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return (string) $this->host;
    }
}
