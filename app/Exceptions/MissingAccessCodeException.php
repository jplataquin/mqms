<?php

namespace App\Exceptions;

use Exception;

class MissingAccessCodeException extends Exception
{
    /**
     * The missing access codes.
     *
     * @var array
     */
    protected array $missingCodes = [];

    /**
     * Create a new exception instance.
     *
     * @param array|string $missingCodes
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($missingCodes = [], string $message = "Access Denied: Missing required access codes.", int $code = 403, ?\Throwable $previous = null)
    {
        $this->missingCodes = is_array($missingCodes) ? $missingCodes : [$missingCodes];
        
        if (!empty($this->missingCodes)) {
            $message = "Access Denied. You are missing the following required access code(s): " . implode(', ', $this->missingCodes);
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the missing access codes.
     *
     * @return array
     */
    public function getMissingCodes(): array
    {
        return $this->missingCodes;
    }
}
