<?php

namespace Msi\CmfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Msi\CmfBundle\Doctrine\Extension\Translatable\TranslatableInterface;
use Msi\CmfBundle\Doctrine\Extension\Timestampable\TimestampableInterface;

/**
 * @ORM\MappedSuperclass
 */
abstract class Page implements TranslatableInterface, TimestampableInterface
{
    use \Msi\CmfBundle\Doctrine\Extension\Timestampable\Traits\TimestampableEntity;
    use \Msi\CmfBundle\Doctrine\Extension\Translatable\Traits\TranslatableEntity;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     */
    protected $template;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $css;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $js;

    /**
     * @ORM\Column(nullable=true, unique=true)
     */
    protected $route;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $home;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $showTitle;

    public function __construct()
    {
        $this->home = false;
        $this->showTitle = true;
        $this->blocks = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function getShowTitle()
    {
        return $this->showTitle;
    }

    public function setShowTitle($showTitle)
    {
        $this->showTitle = $showTitle;

        return $this;
    }

    public function getSite()
    {
        return $this->site;
    }

    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function setBlocks($blocks)
    {
        $this->blocks = $blocks;

        return $this;
    }

    public function getCss()
    {
        return $this->css;
    }

    public function setCss($css)
    {
        $this->css = $css;

        return $this;
    }

    public function getJs()
    {
        return $this->js;
    }

    public function setJs($js)
    {
        $this->js = $js;

        return $this;
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

    public function getHome()
    {
        return $this->home;
    }

    public function setHome($home)
    {
        $this->home = $home;

        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return (string) $this->getTranslation()->getTitle();
    }
}
