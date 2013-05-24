<?php

namespace Msi\CmfBundle\Menu;

use Knp\Menu\FactoryInterface;

class MenuBuilder extends BaseMenuBuilder
{
    public function adminMenu(FactoryInterface $factory, array $options)
    {
        $menu = $this->getMenu($factory, 'admin');

        $menu->setChildrenAttribute('class', 'nav');
        $this->setBootstrapDropdownMenuAttributes($menu);

        return $this->execute($menu);
    }
}
