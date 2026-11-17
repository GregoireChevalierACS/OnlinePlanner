<?php

declare(strict_types=1);

namespace App\Scheduling\Infrastructure\Security;

use App\Identity\Infrastructure\Security\SecurityUser;
use App\Scheduling\Domain\Appointment\AppointmentId;
use App\Scheduling\Domain\Appointment\AppointmentRepository;
use App\Scheduling\Domain\Provider\OwnerId;
use App\Scheduling\Domain\Provider\ProviderRepository;
use App\Scheduling\Domain\ProviderId;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter : seul le propriétaire d'un agenda le consulte et gère ses rendez-vous.
 * La règle elle-même vit dans le domaine (Provider::isManagedBy).
 *
 * @extends Voter<string, string>
 */
final class ScheduleAccessVoter extends Voter
{
    public const string MANAGE_PROVIDER = 'SCHEDULE_MANAGE_PROVIDER';
    public const string MANAGE_APPOINTMENT = 'SCHEDULE_MANAGE_APPOINTMENT';

    public function __construct(
        private readonly ProviderRepository $providers,
        private readonly AppointmentRepository $appointments,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::MANAGE_PROVIDER, self::MANAGE_APPOINTMENT], true) && \is_string($subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof SecurityUser) {
            return false;
        }

        $providerId = self::MANAGE_APPOINTMENT === $attribute
            ? $this->appointments->get(new AppointmentId($subject))->providerId()
            : new ProviderId($subject);

        return $this->providers->get($providerId)->isManagedBy(new OwnerId($user->id));
    }
}
