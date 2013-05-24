<?php

namespace Msi\CmfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="uniq_object_id_locale", columns={"object_id", "locale"})})
 * @ORM\MappedSuperclass
 */
abstract class MenuTranslation
{
    use \Msi\CmfBundle\Doctrine\Extension\Translatable\Traits\TranslationEntity;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     */
    protected $name;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $route;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $published;

    public function __construct()
    {
        $this->published = false;
        $this->route = '#';
    }

    public function getPublished()
    {
        return $this->published;
    }

    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    public function getRealRoute()
    {
        if (preg_match('#^@#', $this->getRoute())) {
            return substr($this->getRoute(), 1);
        }

        return false;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
