<?php

namespace Dagpay;

/**
 * CurlException
 * used to throw the description of problem while connecting to instamojo server.
 * this exception throws when cURL not able to properly execute the request.
 */
class CurlException extends \Exception
{
    private $object;

    public function __construct($message, $curlObject)
    {
        parent::__construct($message, 0);
        $this->object = $curlObject;
    }

    public function __toString()
    {
        # will return curl object from Curl.php in string manner.
        return 'ERROR at Processing cURL request' . PHP_EOL . $this->object;
    }
}