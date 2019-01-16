<?php

namespace Trikoder\Bundle\OAuth2Bundle\Security\Authorization\Voter;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AlwaysApproveAuthRequestVoter extends Voter
{
    protected function supports($attribute, $subject): bool
    {
        return $attribute instanceof AuthorizationRequest;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return Voter::ACCESS_GRANTED;
    }
}
