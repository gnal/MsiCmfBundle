<?php

namespace Msi\CmfBundle\Pager;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\Tools\Pagination\Paginator;

class Pager extends Paginator
{
    protected $options;
    protected $page;
    protected $limit;

    public function __construct($query, array $options = array())
    {
        parent::__construct($query);

        $this->page = 1;
        $this->limit = 10;

        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    public function paginate($page, $limit)
    {
        $this->limit = $limit;
        $this->page = $page;
        $this->getQuery()->setMaxResults($limit);
        $this->getQuery()->setFirstResult(($page - 1) * $limit);
    }

    public function countPages()
    {
        return ceil($this->count() / $this->limit);
    }

    public function getFrom()
    {
        return $this->limit * $this->page - $this->limit + 1;
    }

    public function getTo()
    {
        if ($this->page == $this->countPages()) {
            return $this->count();
        }
        else {
            return $this->limit * $this->page;
        }
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array(),
            'template' => 'bootstrap',
        ));
    }
}
