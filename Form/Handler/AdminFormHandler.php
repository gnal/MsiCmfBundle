<?php

namespace Msi\CmfBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Msi\CmfBundle\Event\EntityEvent;

class AdminFormHandler
{
    protected $request;
    protected $admin;
    protected $dispatcher;

    public function __construct(Request $request, EventDispatcherInterface $dispatcher)
    {
        $this->request = $request;
        $this->dispatcher = $dispatcher;
    }

    public function process($form)
    {
        $entity = $this->admin->getObject();

        $form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            // for uploadify
            if ($this->request->files->all() && !$this->request->files->get($form->getName())) {
                $this->request->files->replace([$form->getName() => ['file' => $this->request->files->get('file')]]);
            }

            $form->bind($this->request);

            if (!$form->isValid()) {
                return false;
            }

            if ($this->admin->hasParent() && !$entity->getId() && $this->admin->getParentObject()->getId()) {
                foreach ($this->admin->getObjectManager()->getMetadata()->associationMappings as $association) {
                    if (in_array($association['type'], [8, 2]) && $association['targetEntity'] === $this->admin->getParent()->getObjectManager()->getClass()) {
                        $relation = $association;
                    }
                }
                if ($relation['type'] === 8) {
                    $getter = 'get'.ucfirst($relation['fieldName']);
                    $entity->$getter()->add($this->admin->getParentObject());
                } else {
                    $setter = 'set'.ucfirst($relation['fieldName']);
                    $getter = 'get'.ucfirst($relation['fieldName']);
                    // test voir si on a manuellement setter le parent dans le form, si oui on ne le remplace pas
                    // bon pour les nested crud de nested set
                    if (!$entity->$getter()) {
                        $entity->$setter($this->admin->getParentObject());
                    }
                }
            }

            if (!$entity->getId()) {
                $this->admin->prePersist($entity);
            } else {
                $this->admin->preUpdate($entity);
            }

            if ($this->admin->getAction() === 'edit') {
                $this->dispatcher->dispatch('msi_cmf.entity.update.success', new EntityEvent($entity, $this->request));
            } else {
                $this->dispatcher->dispatch('msi_cmf.entity.create.success', new EntityEvent($entity, $this->request));
            }

            if ('msi_user_user_admin' === $this->admin->getId()) {
                $this->admin->getContainer()->get('fos_user.user_manager')->updateUser($entity);
            } else {
                $this->admin->getObjectManager()->update($entity);
            }

            if ($this->admin->getAction() === 'edit') {
                $this->admin->postUpdate($entity);
            } else {
                $this->admin->postPersist($entity);
            }

            return true;
        }
    }

    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }
}
