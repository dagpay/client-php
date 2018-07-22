<?php
namespace Dagcoin\PaymentGateway\lib;

class Curl
{
    private $curl;

    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->curl = $curl;
    }

    private function execute()
    {
        $response = $this->curl->getBody();
        return $response;
    }
    public function get($url)
    {
        $this->curl->get($url);

        return $this->execute();
    }

    public function post($url, $data)
    {
        $data_string = json_encode($data);

        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Content-Length", strlen($data_string));
        $this->curl->post($url, $data_string);

        return $this->execute();
    }
}
