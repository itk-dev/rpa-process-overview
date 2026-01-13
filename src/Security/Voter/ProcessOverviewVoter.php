<?php

namespace App\Security\Voter;

use App\Entity\ProcessOverview;
use App\Entity\User;
use App\Entity\UserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ProcessOverviewVoter extends Voter
{
    public const EDIT = 'PROCESS_OVERVIEW_EDIT';
    public const VIEW = 'PROCESS_OVERVIEW_VIEW';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof ProcessOverview;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null,
    ): bool {
        $user = $token->getUser();
        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            $vote?->addReason('The user is not logged in.');

            return false;
        }

        /** @var ProcessOverview $overview */
        $overview = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($overview, $token),
            self::EDIT => $this->canEdit($overview, $token, $vote),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function canView(ProcessOverview $overview, TokenInterface $token): bool
    {
        // if they can edit, they can view
        if ($this->canEdit($overview, $token, null)) {
            return true;
        }

        return $overview->isPublished();
    }

    private function canEdit(ProcessOverview $overview, TokenInterface $token, ?Vote $vote): bool
    {
        if ($this->accessDecisionManager->decide($token, [UserRole::Admin->value])) {
            return true;
        }

        return false;
    }
}
