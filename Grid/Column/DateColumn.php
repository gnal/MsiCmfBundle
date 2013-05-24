<?php

namespace Msi\CmfBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DateColumn extends BaseColumn
{
    public function fixValue()
    {
        $this->value = $this->value->format($this->options['format']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'format' => 'Y/m/d',
        ));
    }
}
