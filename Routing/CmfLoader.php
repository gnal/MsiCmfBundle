<?php

namespace Msi\CmfBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class CmfLoader implements LoaderInterface
{
    private $loaded = false;
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add this loader twice');
        }

        $collection = new RouteCollection();

        foreach ($this->container->getParameter('msi_cmf.admin_ids') as $id) {
            $admin = $this->container->get($id);
            $collection->addCollection($this->buildRoutes($admin));
        }

        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return 'msi_cmf' === $type;
    }

    public function getResolver()
    {
    }

    public function setResolver(LoaderResolverInterface $resolver)
    {
    }

    protected function buildRoutes($admin)
    {
        $collection = new RouteCollection();

        $namespace = preg_replace(['|^[a-z]+_[a-z]+_|', '|_admin$|', '|_|'], ['', '', '-'], $admin->getId());

        $prefix = '/admin/'.$namespace;
        $suffix = '';

        $names = array(
            'list',
            'new',
            'edit',
            'delete',
            'toggle',
            'sort',
            'deleteUpload',
        );

        foreach ($names as $name) {
            $collection->add(
                $admin->getId().'_'.$name,
                new Route(
                    $prefix.'/'.$name.$suffix,
                    array(
                        '_controller' => $admin->getOption('controller').$name,
                        '_admin' => $admin->getId(),
                    )
                )
            );
        }

        $collection->add(
            $admin->getId().'_list',
            new Route(
                $prefix.'/list'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'list',
                    '_admin' => $admin->getId(),
                ),
                array(
                    '_method' => 'GET',
                )
            )
        );

        $collection->add(
            $admin->getId().'_new',
            new Route(
                $prefix.'/new'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'new',
                    '_admin' => $admin->getId(),
                ),
                array(
                    '_method' => 'GET|POST',
                )
            )
        );

        $collection->add(
            $admin->getId().'_edit',
            new Route(
                $prefix.'/{id}/edit'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'edit',
                    '_admin' => $admin->getId(),
                )
            )
        );

        $collection->add(
            $admin->getId().'_delete',
            new Route(
                $prefix.'/{id}/delete'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'delete',
                    '_admin' => $admin->getId(),
                )
            )
        );

        $collection->add(
            $admin->getId().'_softDelete',
            new Route(
                $prefix.'/{id}/soft-delete'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'softDelete',
                    '_admin' => $admin->getId(),
                )
            )
        );

        $collection->add(
            $admin->getId().'_toggle',
            new Route(
                $prefix.'/{id}/toggle'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'toggle',
                    '_admin' => $admin->getId(),
                )
            )
        );

        $collection->add(
            $admin->getId().'_deleteUpload',
            new Route(
                $prefix.'/{id}/delete-upload'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'deleteUpload',
                    '_admin' => $admin->getId(),
                )
            )
        );

        return $collection;
    }
}
