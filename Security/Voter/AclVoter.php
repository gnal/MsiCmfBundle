<?php

namespace Msi\CmfBundle\Security\Voter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AclVoter implements VoterInterface
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function supportsAttribute($attribute)
    {
        return in_array($attribute, ['ACL_UPDATE', 'ACL_DELETE', 'ACL_READ', 'ACL_CREATE']);
    }

    public function supportsClass($class)
    {
        if (null === $class) {
            return false;
        }

        return property_exists($class, 'operators');
    }

    function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = self::ACCESS_ABSTAIN;
        $user = $token->getUser();

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            if ($user->isSuperAdmin() || !$this->supportsClass($object)) {
                return self::ACCESS_GRANTED;
            }

            if ($object->getOperators()->count()) {
                $result = self::ACCESS_DENIED;

                foreach ($object->getOperators() as $group) {
                    if ($user->getGroups()->contains($group)) {
                        $result = self::ACCESS_GRANTED;
                    }
                }
            } else {
                return self::ACCESS_GRANTED;
            }
        }

        return $result;
    }
}
