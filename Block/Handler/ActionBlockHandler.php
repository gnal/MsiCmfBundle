<?php

namespace Msi\AdminBundle\Block\Handler;

use Msi\AdminBundle\Block\BaseBlockHandler;
Symfony\Component\HttpKernel\Fragment\FragmentHandler
use Msi\AdminBundle\Entity\Block;
use Msi\AdminBundle\Entity\Page;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints\NotBlank;

class ActionBlockHandler extends BaseBlockHandler
{
    protected $fragmentHandler;
    protected $actions;

    public function __construct($actions, FragmentHandler $fragmentHandler)
    {
        $this->fragmentHandler = $fragmentHandler;
        $this->actions = $actions;
    }

    public function execute(Block $block, Page $page)
    {
        $settings = $block->getSettings();
        $options = [];

        if (isset($settings['query'])) {
            $parts = explode('&', trim($settings['query']));
            foreach ($parts as $part) {
                $pieces = explode('=', trim($part));
                $options['query'][$pieces[0]] = $pieces[1];
            }
        }

        return $this->fragmentHandler->render($settings['action'], $options);
    }

    public function buildForm(FormBuilder $builder)
    {
        $builder->add('action', 'choice', [
            'choices' => $this->actions,
            'constraints' => new NotBlank(),
        ]);
        $builder->add('query', 'text');
    }

    public function getName()
    {
        return 'action';
    }
}
