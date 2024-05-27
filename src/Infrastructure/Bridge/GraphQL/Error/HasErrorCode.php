<?php

declare(strict_types=1);

namespace App\Infrastructure\Bridge\GraphQL\Error;

interface HasErrorCode
{
    /**
     * @return string the public error code for this error
     */
    public function getErrorCode(): string;
}
