<?php

namespace Msi\CmfBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\Menu\NodeInterface;
use Msi\CmfBundle\Doctrine\Extension\Translatable\TranslatableInterface;
use Msi\CmfBundle\Doctrine\Extension\Timestampable\TimestampableInterface;

/**
 * @ORM\MappedSuperclass
 * @Gedmo\Tree(type="nested")
 */
abstract class Menu implements NodeInterface, TranslatableInterface, TimestampableInterface
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
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(type="integer")
     */
    protected $menu;

    protected $options = array();

    /**
     * @ORM\Column(type="boolean")
     */
    protected $targetBlank;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->targetBlank = false;
        $this->operators = new ArrayCollection();
    }

    public function getOperators()
    {
        return $this->operators;
    }

    public function setOperators($operators)
    {
        $this->operators = $operators;

        return $this;
    }

    public function getTargetBlank()
    {
        return $this->targetBlank;
    }

    public function setTargetBlank($targetBlank)
    {
        $this->targetBlank = $targetBlank;

        return $this;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    public function getOptions()
    {
        $this->options['extras']['groups'] = $this->operators;
        $this->options['extras']['published'] = $this->getTranslation()->getPublished();

        if ($this->page) {
            if (!$this->page->getRoute()) {
                if (!$this->page->getHome()) {
                    $this->options['route'] = 'msi_page_show';
                    $this->options['routeParameters'] = array('slug' => $this->page->getTranslation()->getSlug());
                } else {
                    $this->options['route'] = 'msi_page_home';
                }
            } else {
                $this->options['route'] = $this->page->getRoute();
            }
        } else if (preg_match('#^@#', $this->getTranslation()->getRoute())) {
            $this->options['route'] = substr($this->getTranslation()->getRoute(), 1);
        } else {
            $this->options['uri'] = $this->getTranslation()->getRoute();
        }

        if ($this->targetBlank) {
            $this->options['linkAttributes'] = array('target' => '_blank');
        }

        return $this->options;
    }

    public function setOption($k ,$v)
    {
        $this->options[$k] = $v;
    }

    public function addChild($child)
    {
        $this->children[] = $child;

        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function getName()
    {
        return $this->getTranslation()->getName();
    }

    public function getLvl()
    {
        return $this->lvl;
    }

    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    public function getLft()
    {
        return $this->lft;
    }

    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    public function getRgt()
    {
        return $this->rgt;
    }

    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    public function getMenu()
    {
        return $this->menu;
    }

    public function setMenu($menu)
    {
        $this->menu = $menu;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getToTree()
    {
        $prefix = '';
        for ($i=0; $i < $this->lvl; $i++) {
            $prefix .= '- ';
        }

        if ($this->lvl === 0) {
            $name = $prefix.'Root';
        } else {
            $name = $prefix.$this->getTranslation()->getName();
        }

        return $name;
    }

    public function __toString()
    {
        return (string) $this->getTranslation()->getName();
    }
}
