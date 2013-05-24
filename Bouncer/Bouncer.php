<?php

namespace Msi\CmfBundle\Bouncer;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\Common\Collections\ArrayCollection;

class Bouncer
{
    protected $user;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->user = $securityContext->getToken()->getUser();
    }

    public function operatorFilter(ArrayCollection $collection)
    {
        if ($this->user->isSuperAdmin()) {
            return;
        }

        foreach ($collection as $element) {
            $i = 1;
            foreach ($element->getOperators() as $group) {
                if ($this->user->getGroups()->contains($group)) {
                    break;
                }
                if ($i === $element->getOperators()->count()) {
                    $collection->removeElement($element);
                }
                $i++;
            }
        }
    }
}
