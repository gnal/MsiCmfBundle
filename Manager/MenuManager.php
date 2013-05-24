<?php

namespace Msi\CmfBundle\Manager;

use Msi\CmfBundle\Doctrine\Manager as BaseManager;

class MenuManager extends BaseManager
{
    public function findRootByName($name)
    {
        $qb = $this->getFindByQueryBuilder(
            array(
                't.name' => $name,
            ),
            array(
                'a.children' => 'lvl1',
                'lvl1.children' => 'lvl2',
                'lvl2.children' => 'lvl3',

                'a.translations' => 't',
                'lvl1.translations' => 'lvl1t',
                'lvl2.translations' => 'lvl2t',
                'lvl3.translations' => 'lvl3t',

                'a.operators' => 'g',
                'lvl1.operators' => 'lvl1g',
                'lvl2.operators' => 'lvl2g',
                'lvl3.operators' => 'lvl3g',

                'a.page' => 'p',
                'lvl1.page' => 'p1',
                'lvl2.page' => 'p2',
                'lvl3.page' => 'p3',

                'p.translations' => 'pt',
                'p1.translations' => 'p1t',
                'p2.translations' => 'p2t',
                'p3.translations' => 'p3t',
            )
        );

        // $orX = $qb->expr()->orX();

        // $orX->add($qb->expr()->eq('pt.locale', ':ptlocale'));
        // $qb->setParameter('ptlocale', $locale);

        // $orX->add($qb->expr()->isNull('c.page'));

        // $qb->andWhere($orX);

        // $qb->andWhere($qb->expr()->eq('ct.locale', ':ctlocale'));
        // $qb->setParameter('ctlocale', $locale);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
