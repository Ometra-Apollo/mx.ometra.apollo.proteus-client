<?php

namespace Ometra\Apollo\Proteus\Exceptions;

use Exception;
use Throwable;

class ProteusException extends Exception
{
    private ?array $response;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?array $response = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }
}
