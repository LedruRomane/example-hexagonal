<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Listener;

use App\Infrastructure\Bridge\GraphQL\Error\CustomUserError;
use App\Infrastructure\Bridge\GraphQL\Error\HasErrorCode;
use App\Infrastructure\Bridge\GraphQL\Error\InvalidPayloadError;
use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Overblog\GraphQLBundle\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Add extra information like constraint violations details in an API-problem like format.
 *
 * @see https://github.com/overblog/GraphQLBundle/blob/0.15/docs/events/index.md#error-formatting
 */
class ErrorFormattingListener implements EventSubscriberInterface
{
    private ConstraintViolationListNormalizer $violationListNormalizer;

    public function __construct(private readonly TranslatorInterface $translator)
    {
        $this->violationListNormalizer = new ConstraintViolationListNormalizer();
    }

    /**
     * @return \ArrayObject<string, mixed>
     */
    public function onErrorFormatting(ErrorFormattingEvent $event): \ArrayObject
    {
        $error = $event->getError();
        $formattedError = $event->getFormattedError();
        $previous = $error->getPrevious();

        // Add extra code
        if ($previous instanceof HasErrorCode) {
            $formattedError['code'] = $previous->getErrorCode();
        }

        // Add violations
        if ($previous instanceof InvalidPayloadError) {
            $formattedError['api_problem'] = $this->violationListNormalizer->normalize($previous->getViolations());
        }

        // Translate custom user error message
        if ($previous instanceof CustomUserError) {
            $formattedError['message'] = $previous->getTranslatableMessage()->trans($this->translator);
        }

        // Move trace after (for response readability in debug mode):
        if (isset($formattedError['trace'])) {
            $trace = $formattedError['trace'];
            unset($formattedError['trace']);
            $formattedError['trace'] = $trace;
        }

        return $formattedError;
    }

    public static function getSubscribedEvents(): array
    {
        return [Events::ERROR_FORMATTING => 'onErrorFormatting'];
    }
}
