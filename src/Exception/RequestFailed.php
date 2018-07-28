<?php

namespace Scriptotek\Alma\Exception;

class RequestFailed extends ClientException
{
    /**
     * @var string
     */
    private $errorCode;

    /**
     * RequestFailed constructor.
     * @param string $message
     * @param string $errorCode
     * @param \Exception|null $previous
     */
    public function __construct($message, $errorCode = null, \Exception $previous = null)
    {
        // Since the error code is generally a string, we store it in a local property
        // rather than passing it on to the parent constructor, which expects an integer.
        $this->errorCode = $errorCode;

        parent::__construct($message, 0, $previous);
    }

    /**
     * Returns the error code string.
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
