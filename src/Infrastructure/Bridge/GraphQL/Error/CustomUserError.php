<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Error;

use App\Infrastructure\Bridge\GraphQL\Listener\ErrorFormattingListener;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * A custom error with a message that can be safely exposed to the user.
 * The message is automatically translated by the {@link ErrorFormattingListener}
 */
class CustomUserError extends UserErrorWithCode
{
    public const ERROR_CODE = 'CUSTOM_USER_ERROR';

    private TranslatableMessage $translatableMessage;

    public function __construct(string|TranslatableMessage $message, \Throwable $previous = null)
    {
        parent::__construct((string) $message, self::ERROR_CODE, $previous);

        if ($message instanceof TranslatableMessage) {
            $this->translatableMessage = $message;
        } else {
            $this->translatableMessage = new TranslatableMessage($message);
        }
    }

    public function getTranslatableMessage(): TranslatableMessage
    {
        return $this->translatableMessage;
    }
}
