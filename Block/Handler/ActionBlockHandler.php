<?php

namespace Msi\CmfBundle\Block\Handler;

use Msi\CmfBundle\Block\BaseBlockHandler;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Msi\CmfBundle\Entity\Block;
use Msi\CmfBundle\Entity\Page;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

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
        $options['query'] = [];

        if (isset($settings['query'])) {
            $parts = explode('&', trim($settings['query']));
            foreach ($parts as $part) {
                $pieces = explode('=', trim($part));
                $options['query'][$pieces[0]] = $pieces[1];
            }
        }

        return $this->fragmentHandler->render(new ControllerReference($settings['action'], [], $options['query']));
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
