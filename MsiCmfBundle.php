<?php

namespace Msi\CmfBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Msi\CmfBundle\DependencyInjection\Compiler\FindAdminPass;
use Msi\CmfBundle\Doctrine\Mapping\DefaultNamingStrategy;

class MsiCmfBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FindAdminPass());
    }

    public function boot()
    {
        $this->container->get('doctrine')->getManager()->getConfiguration()->setNamingStrategy(new DefaultNamingStrategy());
    }
}
