<?php

namespace Msi\CmfBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DynamicType extends AbstractType
{
    protected $name;
    protected $options;
    protected $builder;

    public function __construct($name, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->builder->all() as $child) {
            $builder->add($child->getName(), $child->getType()->getInnerType(), $child->getOptions());
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults($this->options);
    }

    public function getBuilder()
    {
        return $this->builder;
    }

    public function setBuilder($builder)
    {
        $this->builder = $builder;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
}
