<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\Symfony\Mailer\Listener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Configures the default "from" for all emails fi not provided explicitly.
 */
class DefaultFromListener implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire('%env(MAILER_DEFAULT_FROM_NAME)%')]
        private readonly string $defaultFromName,
        #[Autowire('%env(MAILER_DEFAULT_FROM_EMAIL)%')]
        private readonly string $defaultFromEmail,
    ) {
    }

    public function onMessage(MessageEvent $event): void
    {
        $message = $event->getMessage();

        if (!$message instanceof Email) {
            // Only care for emails, not every raw messages
            return;
        }

        if (\count($message->getFrom()) > 0) {
            // Noop, a sender is already configured
            return;
        }

        // Add the default sender, as none were provided explicitly:
        $message->from($this->getDefaultFromAddress());
    }

    private function getDefaultFromAddress(): Address
    {
        return new Address($this->defaultFromEmail, $this->defaultFromName);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => 'onMessage',
        ];
    }
}
