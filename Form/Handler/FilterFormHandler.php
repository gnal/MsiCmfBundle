<?php

namespace Msi\CmfBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;

class FilterFormHandler
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function process($form, $entity, QueryBuilder $qb)
    {
        $filter = $this->request->query->get('filter');
        $metadata = $qb->getEntityManager()->getClassMetadata(get_class($entity));

        if (!$filter) {
            return;
        }

        $form->bind($this->request);

        $i = 1;
        foreach ($filter as $field => $value) {
            if ($field === '_token') {
                continue;
            }

            // do nothing with hard coded filters
            if (!in_array($field, $metadata->fieldNames) && !array_key_exists($field, $metadata->associationMappings)) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                $orX = $qb->expr()->orX();
                $qb->leftJoin('a.'.$field, $field);
                foreach ($value as $id) {
                    if ($id) {
                        $orX->add($qb->expr()->eq($field.'.id', ':filter'.$i));
                        $qb->setParameter('filter'.$i, $id);
                    }
                }
                $qb->andWhere($orX);
            } else {
                if (isset($metadata->associationMappings[$field])) {
                    switch ($metadata->associationMappings[$field]['type']) {
                        case 8:
                            $qb->leftJoin('a.'.$field, $field.$i);
                            $qb->andWhere($field.'.id = :filter'.$i)->setParameter('filter'.$i, $value);
                        case 2:
                            $qb->leftJoin('a.'.$field, $field);
                            $qb->andWhere($field.'.id = :filter'.$i)->setParameter('filter'.$i, $value);
                    }
                } else {
                    $qb->andWhere('a.'.$field.' = :filter'.$i)->setParameter('filter'.$i, $value);
                }
            }
            $i++;
        }
    }
}
