<?php

namespace Msi\CmfBundle\Controller\Admin;

use Msi\CmfBundle\Controller\CoreController;
use Doctrine\ORM\QueryBuilder;

class MenuNodeController extends CoreController
{
    public function promoteAction()
    {
        $node = $this->admin->getObjectManager()->getFindByQueryBuilder(array('a.id' => $this->admin->getObject()->getId()))->getQuery()->getOneOrNullResult();
        $this->admin->getObjectManager()->moveUp($node);

        return $this->getResponse();
    }

    public function demoteAction()
    {
        $node = $this->admin->getObjectManager()->getFindByQueryBuilder(array('a.id' => $this->admin->getObject()->getId()))->getQuery()->getOneOrNullResult();
        $this->admin->getObjectManager()->moveDown($node);

        return $this->getResponse();
    }
}
