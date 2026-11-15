<?php

namespace App\Exceptions;

use RuntimeException;

class PasswordResetDeliveryException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('We could not send your password reset email. Please try again later.');
    }
}
