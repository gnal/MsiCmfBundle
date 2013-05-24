<?php

namespace Msi\CmfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="uniq_object_id_locale", columns={"object_id", "locale"})})
 * @ORM\MappedSuperclass
 */
abstract class SiteTranslation
{
    use \Msi\CmfBundle\Doctrine\Extension\Translatable\Traits\TranslationEntity;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $metaKeywords;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $metaDescription;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $brand;

    public function getBrand()
    {
        return $this->brand;
    }

    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }
}
