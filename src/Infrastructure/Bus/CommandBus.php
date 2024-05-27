<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus;

use App\Application\Bus\CommandBusInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

class CommandBus implements CommandBusInterface
{
    use HandleTrait {
        handle as private handleMessage;
    }

    private MessageBusInterface $messageBus;

    public function __construct(
        #[Autowire(service: 'messenger.bus.commands')]
        MessageBusInterface $messageBus,
    ) {
        $this->messageBus = $messageBus;
    }

    public function handle(object $message): mixed
    {
        return $this->handleMessage($message);
    }

    public function dispatchAsync(object $message): void
    {
        $this->messageBus->dispatch($message);
    }

    public function dispatchEvent(object $event): void
    {
        $envelope = Envelope::wrap($event, [
            // See https://symfony.com/doc/current/messenger/message-recorder.html
            new DispatchAfterCurrentBusStamp(),
        ]);

        $this->messageBus->dispatch($envelope);
    }
}
