<?php

namespace Msi\CmfBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BooleanColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'toggle' => true,
            'attr' => ['style' => 'text-align:center;', 'class' => 'span2'],
            'badge_true' => 'badge-success',
            'badge_false' => '',
            'icon_true' => 'icon-ok',
            'icon_false' => 'icon-ok',
        ]);
    }
}
