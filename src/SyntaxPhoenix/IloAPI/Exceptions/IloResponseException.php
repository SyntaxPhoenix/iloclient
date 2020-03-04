<?php

namespace src\SyntaxPhoenix\IloAPI\Exceptions;

use Exception;

class IloResponseException extends Exception
{
    
    public function __construct($message, $statusCode = 200)
    {
        parent::__construct($message, $statusCode);
    }

}