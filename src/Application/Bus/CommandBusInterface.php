<?php

declare(strict_types=1);

namespace App\Application\Bus;

interface CommandBusInterface
{
    /**
     * Use to execute the command right now, and expect a **single handler** & result.
     *
     * @return mixed Any returned data from the single handler.
     */
    public function handle(object $message): mixed;

    /**
     * Use whenever you want to execute a command async,
     * but **please ensure the Messenger routing is correct**.
     */
    public function dispatchAsync(object $message): void;

    /**
     * Use this method whenever you're handling a message with "side-effects",
     * i.e events that should be executed **once and only if** the main message was handled.
     * The event can be processed sync or async,
     * but **please ensure the Messenger routing is correct for the later case**.
     *
     * @see https://symfony.com/doc/current/messenger/message-recorder.html
     */
    public function dispatchEvent(object $event): void;
}
