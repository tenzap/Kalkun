<?php

namespace App\Exceptions;

use CodeIgniter\Exceptions\HTTPExceptionInterface;
use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\Exceptions\PageNotFoundException;

class KalkunException extends RuntimeException implements HTTPExceptionInterface
{
	public function __construct($message = '', int $code = 0, ?Throwable $previous = null)
	{
		$this->message = $message;
		$this->code = $code;
	}

}
