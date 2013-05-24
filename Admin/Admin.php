<?php

namespace Msi\CmfBundle\Admin;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Msi\CmfBundle\Doctrine\Manager;
use Doctrine\Common\Collections\ArrayCollection;
use Msi\CmfBundle\Form\Type\DynamicType;
use Msi\CmfBundle\Doctrine\Extension\Translatable\TranslatableInterface;

use Symfony\Component\Form\FormBuilder;
use Msi\CmfBundle\Grid\GridBuilder;
use Doctrine\ORM\QueryBuilder;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class Admin
{
    protected $options = [];

    protected $id;
    protected $children = [];
    protected $parent;
    protected $entity;
    protected $parentEntity;
    protected $container;
    protected $objectManager;
    protected $forms;
    protected $grids;

    public function __construct(Manager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    abstract public function buildGrid(GridBuilder $builder);

    public function buildForm(FormBuilder $builder)
    {
    }

    public function buildTranslationForm(FormBuilder $builder)
    {
    }

    public function buildFilterForm(FormBuilder $builder)
    {
    }

    public function buildListQuery(QueryBuilder $qb)
    {
    }

    public function configure()
    {
    }

    public function prePersist($entity)
    {
    }

    public function postPersist($entity)
    {
    }

    public function preUpdate($entity)
    {
    }

    public function postUpdate($entity)
    {
    }

    public function postLoad(ArrayCollection $collection)
    {
    }

    public function getLabel($number = 1, $locale = null)
    {
        $class = get_class($this);
        $class = substr($class, strrpos($class, '\\') + 1);
        $class = substr($class, 0, -5);

        return $this->container->get('translator')->transChoice('entity.'.$class, $number, [], 'messages', $locale);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        $this->configure();
        $this->init();

        return $this;
    }

    public function getBundleName()
    {
        $parts = explode('_', $this->id);

        return ucfirst($parts[0]).ucfirst($parts[1]).'Bundle';
    }

    public function getAction()
    {
        return preg_replace(['#^[a-z]+_([a-z]+_){1,2}[a-z]+_[a-z]+_#'], [''], $this->container->get('request')->attributes->get('_route'));
    }

    public function isSortable()
    {
        return property_exists($this->getObjectManager()->getClass(), 'position');
    }

    public function isTranslatable()
    {
        return $this->getObject() instanceof TranslatableInterface;
    }

    public function isTranslationField($field)
    {
        if (!$this->isTranslatable()) {
            return false;
        }

        return property_exists($this->getObject()->getTranslation(), $field);
    }

    public function getClass()
    {
        return $this->getObjectManager()->getClass();
    }

    public function getClassName()
    {
        return substr($this->getObjectManager()->getClass(), strrpos($this->getObjectManager()->getClass(), '\\') + 1);
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }

    public function getObject()
    {
        if (!$this->object) {
            $this->object = $this->objectManager->findOneOrCreate(
                $this->container->getParameter('msi_cmf.app_locales'),
                $this->container->get('request')->attributes->get('id')
            );
        }

        return $this->object;
    }

    public function getParentObject()
    {
        if (!$this->parentObject) {
            $this->parentObject = $this->getParent()->objectManager->findOneOrCreate(
                $this->container->getParameter('msi_cmf.app_locales'),
                $this->container->get('request')->query->get('parentId')
            );
        }

        return $this->parentObject;
    }

    public function getOption($key, $default = null)
    {
        return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    public function hasChild($id)
    {
        return array_key_exists($id, $this->children);
    }

    public function addChild(Admin $child)
    {
        $this->children[$child->getId()] = $child;

        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function hasParent()
    {
        return $this->parent instanceof Admin;
    }

    public function setParent(Admin $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function createGridBuilder()
    {
        return new GridBuilder();
    }

    public function getGrid($name = '')
    {
        if (!isset($this->grids[$name])) {
            $method = 'build'.ucfirst($name).'Grid';

            if (!method_exists($this, $method)) return false;

            $builder = $this->createGridBuilder();
            $this->$method($builder);
            $this->grids[$name] = $builder->getGrid();
        }

        return $this->grids[$name];
    }

    public function createFormBuilder($name, $data = null, array $options = array())
    {
        $name = $name ?: preg_replace(['|^[a-z]+_[a-z]+_|', '|_admin$|'], ['', ''], $this->id);

        return $this->container->get('form.factory')->createNamedBuilder($name, 'form', $data, $options);
    }

    public function getForm($name = '', $csrf = false)
    {
        if (!isset($this->forms[$name])) {
            $method = 'build'.ucfirst($name).'Form';

            $builder = $this->createFormBuilder($name, $name ? null : $this->getObject(), array('csrf_protection' => $csrf, 'cascade_validation' => true));

            $this->$method($builder);

            if (!$name && $this->getObject() instanceof TranslatableInterface) {
                $type = (new DynamicType('translation', ['data_class' => $this->getClass().'Translation']))->setBuilder($this->container->get('form.factory')->createBuilder());
                $this->buildTranslationForm($type->getBuilder());
                if ($type->getBuilder()->all()) {
                    $builder->add('translations', 'collection', [
                        'label' => ' ',
                        'type' => $type,
                        'options' => [
                            'label' => ' ',
                        ]
                    ]);
                }
            }

            $this->forms[$name] = $builder->getForm();
        }

        return $this->forms[$name];
    }

    public function isGranted($role)
    {
        if (!$this->container->get('security.context')->isGranted('ROLE_SUPER_ADMIN') && !$this->container->get('security.context')->isGranted(strtoupper('ROLE_'.$this->id.'_'.$role))) {
            return false;
        } else {
            return true;
        }
    }

    public function genUrl($route, $parameters = array(), $mergePersistentParameters = true, $absolute = false)
    {
        if (true === $mergePersistentParameters) {
            $query = $this->container->get('request')->query;
            $persistant = array(
                'page' => $query->get('page'),
                'q' => $query->get('q'),
                'parentId' => $query->get('parentId'),
                'filter' => $query->get('filter'),
            );
            $parameters = array_merge($persistant, $parameters);
        }

        return $this->container->get('router')->generate($this->id.'_'.$route, $parameters, $absolute);
    }

    public function buildBreadcrumb()
    {
        $request = $this->container->get('request');
        $action = $this->getAction();
        $crumbs = [];

        if ($this->hasParent()) {
            $crumbs[] = ['label' => $this->getParent()->getLabel(2), 'path' => $this->getParent()->genUrl('list', [], false)];
            $crumbs[] = ['label' => $this->getParentObject(), 'path' => $this->getParent()->genUrl('edit', ['id' => $this->getParentObject()->getId()], false)];
        }

        $crumbs[] = [
            'label' => $this->getLabel(2),
            'path' => 'list' !== $action ? $this->genUrl('list') : '',
            'class' => 'list' === $action ? 'active' : '',
        ];

        if ($action === 'new') {
            $crumbs[] = array('label' => $this->container->get('translator')->trans('Add'), 'path' => '', 'class' => 'active');
        }

        if ($action === 'edit') {
            $crumbs[] = array('label' => $this->container->get('translator')->trans('Edit'), 'path' => '', 'class' => 'active');
        }

        if ($action === 'show') {
            $crumbs[] = array('label' => $this->getObject(), 'path' => '', 'class' => 'active');
        }

        if ($action === 'list' && $this->hasParent()) {
            $crumbs[] = [
                'label' => $this->container->get('translator')->trans('Back'),
                'path' => $this->getParent()->genUrl('list'),
                'class' => 'pull-right',
            ];
        } elseif ($action === 'list' && !$this->hasParent()) {
        } else {
            $crumbs[] = [
                'label' => $this->container->get('translator')->trans('Back'),
                'path' => $this->genUrl('list'),
                'class' => 'pull-right',
            ];
        }

        return $crumbs;
    }

    protected function init()
    {
        $this->object = null;
        $this->parentObject = null;
        $this->forms = [];
        $this->tables = [];

        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($this->options);
    }

    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'controller'        => 'MsiCmfBundle:Core:',
            'form_template'     => 'MsiCmfBundle:Admin:form.html.twig',
            'index_template'    => 'MsiCmfBundle:Admin:index.html.twig',
            'new_template'      => 'MsiCmfBundle:Admin:new.html.twig',
            'edit_template'     => 'MsiCmfBundle:Admin:edit.html.twig',
            'search_fields'     => ['a.id'],
            'order_by'          => ['a.id' => 'DESC'],
            'uploadify'         => false,
        ]);

        $resolver->setOptional([
            'form_css_template',
            'form_js_template',
        ]);

        if ($this->hasParent()) {
            $resolver->setRequired(['icon']);
        }
    }
}
