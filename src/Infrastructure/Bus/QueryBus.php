<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus;

use App\Application\Bus\QueryBusInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class QueryBus implements QueryBusInterface
{
    use HandleTrait {
        handle as public query;
    }

    public function __construct(
        #[Autowire(service: 'messenger.bus.queries')]
        MessageBusInterface $messageBus,
    ) {
        $this->messageBus = $messageBus;
    }
}
