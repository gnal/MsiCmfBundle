<?php

namespace Msi\CmfBundle\Doctrine;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\QueryBuilder;
use Msi\CmfBundle\Doctrine\Extension\Translatable\TranslatableInterface;

class Manager
{
    protected $em;
    protected $repository;
    protected $class;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function update($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function updateBatch($entity, $i, $batchSize = 20)
    {
        $this->em->persist($entity);
        if ($i % $batchSize === 0) {
            $this->em->flush();
            $this->em->clear();
        }
    }

    public function delete($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function createTranslations($entity, array $locales)
    {
        $class = $this->class.'Translation';
        foreach ($locales as $locale) {
            if (!$entity->hasTranslation($locale)) {
                $translation = new $class();
                $translation->setLocale($locale)->setObject($entity);
                $entity->getTranslations()->add($translation);
            }
        }
    }

    public function toggle($entity, $request)
    {
        $field = $request->query->get('field');
        $locale = $request->query->get('locale');

        $getter = 'get'.ucfirst($field);
        $setter = 'set'.ucfirst($field);

        if ($locale) {
            $entity->getTranslation($locale)->$getter()
                ? $entity->getTranslation($locale)->$setter(false)
                : $entity->getTranslation($locale)->$setter(true);
        } else {
            $entity->$getter() ? $entity->$setter(false) : $entity->$setter(true);
        }

        $this->update($entity);
    }

    public function moveUp($entity)
    {
        $this->repository->moveUp($entity, 1);
        $this->update($entity);
    }

    public function moveDown($entity)
    {
        $this->repository->moveDown($entity, 1);
        $this->update($entity);
    }

    public function create()
    {
        return new $this->class();
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getMetadata()
    {
        return $this->em->getClassMetadata($this->class);
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($this->class);

        return $this;
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    public function findOneOrCreate(array $locales, $id = null)
    {
        if ($id) {
            $object = $this->getOneBy(['a.id' => $id]);
        } else {
            $object = $this->create();
        }

        if ($object instanceof TranslatableInterface) {
            $this->createTranslations($object, $locales);
        }

        return $object;
    }

    public function getOneBy(array $where, array $join = [], $throw = true)
    {
        $result = $this->getFindByQueryBuilder($where, $join)->getQuery()->getOneOrNullResult();
        if ($throw && !$result) {
            throw new NotFoundHttpException('getOneBy method says: '.$this->class.' was not found');
        }

        return $result;
    }

    public function getFindByQueryBuilder(array $where = [], array $join = [], array $orderBy = [], $limit = null, $offset = null)
    {
        $qb = $this->repository->createQueryBuilder('a');

        $qb = $this->buildFindBy($qb, $where, $join, $orderBy);

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        return $qb;
    }

    public function getSearchQueryBuilder($q, array $searchFields, array $where = [], array $join = [], array $orderBy = [], $explode = true)
    {
        $qb = $this->repository->createQueryBuilder('a');

        if (count($searchFields)) {
            $q = trim($q);
            // $strings = $explode ? explode(' ', $q) : [$q];
            $strings = [$q];

            $orX = $qb->expr()->orX();
            $i = 1;
            foreach ($searchFields as $field) {
                foreach ($strings as $string) {
                    $token = 'likeMatch'.$i;
                    $orX->add($qb->expr()->like($field, ':'.$token));
                    $qb->setParameter($token, '%'.$string.'%');

                    $orX->add($qb->expr()->like('a.id', ':eqMatchForId'.$i));
                    $qb->setParameter('eqMatchForId'.$i, $string);
                    $i++;
                }
            }

            $qb->andWhere($orX);
        }

        $qb = $this->buildFindBy($qb, $where, $join, $orderBy);

        return $qb;
    }

    protected function buildFindBy(QueryBuilder $qb, array $where, array $join, array $orderBy)
    {
        $i = 1;
        foreach ($where as $k => $v) {
            $token = 'eqMatch'.$i;
            $qb->andWhere($qb->expr()->eq($k, ':'.$token))->setParameter($token, $v);
            $i++;
        }

        foreach ($join as $k => $v) {
            $qb->leftJoin($k, $v);
            $qb->addSelect($v);
        }

        foreach ($orderBy as $k => $v) {
            $qb->addOrderBy($k, $v);
        }

        return $qb;
    }
}
