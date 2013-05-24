<?php

namespace Msi\CmfBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ActionColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'tree' => false,
            'soft_delete' => false,
            'actions' => [],
            'attr' => ['class' => 'span1'],
        ]);
    }
}
