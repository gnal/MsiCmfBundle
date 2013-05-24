<?php

namespace Msi\CmfBundle\Block\Handler;

use Msi\CmfBundle\Block\BaseBlockHandler;
use Msi\CmfBundle\Entity\Block;
use Msi\CmfBundle\Entity\Page;
use Symfony\Component\Form\FormBuilder;
use Knp\Menu\Renderer\RendererInterface;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

use Msi\CmfBundle\Menu\BaseMenuBuilder;

class MenuBlockHandler extends BaseBlockHandler
{
    protected $menuFactory;
    protected $listRenderer;
    protected $menuManager;

    public function __construct(FactoryInterface $menuFactory, RendererInterface $listRenderer, $menuManager)
    {
        $this->menuFactory = $menuFactory;
        $this->listRenderer = $listRenderer;
        $this->menuManager = $menuManager;
    }

    public function execute(Block $block, Page $page)
    {
        $node = $this->menuManager->getOneBy(['a.id' => $block->getSettings()['menu']], [], false);
        $options = [
            'allow_safe_labels' => true,
            'depth' => 3,
            'currentClass' => 'active',
        ];

        if (!$node) {
            return 'Menu ID '.$block->getSettings()['menu'].' was not found.';
        }

        $builder = new BaseMenuBuilder();
        $item = $builder->create($this->menuFactory, $node);
        $builder->setBootstrapDropdownMenuAttributes($item);
        $builder->execute($item);

        $item->setChildrenAttribute('class', $block->getSettings()['class']);

        return $this->listRenderer->render($item, $options);
    }

    public function buildForm(FormBuilder $builder)
    {
        $nodes = $this->menuManager->getFindByQueryBuilder(
            ['a.lvl' => 0],
            ['a.translations' => 't'],
            ['t.name' => 'ASC']
        )->getQuery()->execute();

        foreach ($nodes as $node) {
            $choices[$node->getId()] = $node;
        }

        $builder->add('menu', 'choice', [
            'constraints' => new NotBlank(),
            'choices' => $choices,
        ]);

        $builder->add('class');
    }

    public function getName()
    {
        return 'menu';
    }
}
