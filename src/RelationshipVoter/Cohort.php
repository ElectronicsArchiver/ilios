<?php

declare(strict_types=1);

namespace App\RelationshipVoter;

use App\Classes\SessionUserInterface;
use App\Entity\CohortInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class Cohort extends AbstractVoter
{
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof CohortInterface
            && in_array($attribute, [self::VIEW, self::EDIT]);
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
                return true;
                break;
            case self::EDIT:
                return $this->permissionChecker->canUpdateProgramYear($user, $subject->getProgramYear());
                break;
        }

        return false;
    }
}
