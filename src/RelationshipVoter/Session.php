<?php

declare(strict_types=1);

namespace App\RelationshipVoter;

use App\Classes\SessionUserInterface;
use App\Entity\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class Session extends AbstractVoter
{
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof SessionInterface
            && in_array($attribute, [self::CREATE, self::VIEW, self::EDIT, self::DELETE]);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof SessionUserInterface) {
            return false;
        }
        if ($user->isRoot()) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW:
                return $user->performsNonLearnerFunction();
                break;
            case self::EDIT:
                return $this->permissionChecker->canUpdateSession($user, $subject);
                break;
            case self::CREATE:
                return $this->permissionChecker->canCreateSession($user, $subject->getCourse());
                break;
            case self::DELETE:
                return $this->permissionChecker->canDeleteSession($user, $subject);
                break;
        }

        return false;
    }
}
