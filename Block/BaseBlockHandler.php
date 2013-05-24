<?php

namespace Msi\CmfBundle\Block;

use Msi\CmfBundle\Entity\Block;
use Msi\CmfBundle\Entity\Page;
use Symfony\Component\Form\FormBuilder;

abstract class BaseBlockHandler
{
    abstract public function execute(Block $block, Page $page);

    abstract public function getName();

    public function buildForm(FormBuilder $builder)
    {
    }

    public function buildTranslationForm(FormBuilder $builder)
    {
    }

    public function setDefaultTranslationOptions(OptionsResolverInterface $resolver)
    {
    }
}
