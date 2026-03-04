<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Event>
 */
class EventVoter extends Voter
{
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE], true)
            && $subject instanceof Event;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Event $event */
        $event = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($event, $user);
            case self::DELETE:
                return $this->canDelete($event, $user);
        }

        return false;
    }

    private function canEdit(Event $event, User $user): bool
    {
        $owner = $event->getUser();
        if (!$owner instanceof User) {
            return false;
        }

        return $user->getId() === $owner->getId();
    }

    private function canDelete(Event $event, User $user): bool
    {
        $owner = $event->getUser();
        if (!$owner instanceof User) {
            return false;
        }

        return $user->getId() === $owner->getId();
    }
}
