<?php

namespace Msi\CmfBundle\Block\Handler;

use Msi\CmfBundle\Block\BaseBlockHandler;
use Msi\CmfBundle\Entity\Block;
use Msi\CmfBundle\Entity\Page;
use Symfony\Component\Form\FormBuilder;

class TextBlockHandler extends BaseBlockHandler
{
    public function execute(Block $block, Page $page)
    {
        return $block->getTranslation()->getSettings()['body'];
    }

    public function buildTranslationForm(FormBuilder $builder)
    {
        $builder->add('body', 'textarea', ['attr' => ['class' => 'tinymce']]);
    }

    public function getName()
    {
        return 'text';
    }
}
