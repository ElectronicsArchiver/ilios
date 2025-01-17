<?php

declare(strict_types=1);

namespace App\RelationshipVoter;

use App\Classes\SessionUserInterface;
use App\Entity\CourseInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class Course extends AbstractVoter
{
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof CourseInterface
            && in_array($attribute, [
                self::CREATE,
                self::VIEW,
                self::EDIT,
                self::DELETE,
                self::UNLOCK,
                self::LOCK,
                self::ARCHIVE,
            ]);
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
            case self::CREATE:
                return $this->permissionChecker->canCreateCourse($user, $subject->getSchool());
                break;
            case self::EDIT:
                return $this->permissionChecker->canUpdateCourse($user, $subject);
                break;
            case self::DELETE:
                return $this->permissionChecker->canDeleteCourse($user, $subject);
                break;
            case self::UNLOCK:
                return $this->permissionChecker->canUnlockCourse($user, $subject);
                break;
            case self::ARCHIVE:
                return $this->permissionChecker->canArchiveCourse($user, $subject);
                break;
            case self::LOCK:
                return $this->permissionChecker->canLockCourse($user, $subject);
                break;
        }

        return false;
    }
}
