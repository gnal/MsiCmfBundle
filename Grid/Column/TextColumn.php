<?php

namespace Msi\CmfBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TextColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'truncate' => true,
            'truncate_length' => 30,
            'truncate_preserve' => false,
            'truncate_separator' => '...',
        ));
    }
}
