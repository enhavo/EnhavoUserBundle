<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\UserBundle\EventListener;

use Enhavo\Bundle\UserBundle\Configuration\ConfigurationProvider;
use Enhavo\Bundle\UserBundle\Event\UserEvent;
use Enhavo\Bundle\UserBundle\Exception\VerificationRequiredException;
use Enhavo\Bundle\UserBundle\Model\UserInterface;
use Enhavo\Bundle\UserBundle\User\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author blutze
 */
class VerificationRequiredListenerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserManager $userManager,
        private ConfigurationProvider $configurationProvider,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvent::PRE_AUTH => 'onPreAuth',
        ];
    }

    public function onPreAuth(UserEvent $event): void
    {
        $user = $event->getUser();

        if ($user instanceof UserInterface && $this->isVerificationRequired($user)) {
            $exception = new VerificationRequiredException('Verification required');
            $exception->setUser($event->getUser());
            $event->setException($exception);
            $this->userManager->requestVerification($user, $this->configurationProvider->getVerificationRequestConfiguration());
        }
    }

    private function isVerificationRequired(UserInterface $user): bool
    {
        $configuration = $this->configurationProvider->getLoginConfiguration();

        return $configuration->isVerificationRequired() && !$user->isVerified();
    }
}
