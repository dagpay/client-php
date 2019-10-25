<?php
/**
 * Dagpay client-php
 * Copyright (C) 2019 VisionCraft
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Dagpay;

class Curl
{
    private $ch;
    private $useragent;
    private $referer;
    private $showRequestHeaders;
    private $showResponseHeaders;
    private $debug;
    private $cacert;
    private $url;
    private $data;
    private $requestMethod;
    private $responseCode;
    private $headers;
    private $error;

    public function __construct()
    {
        $this->ch = curl_init();
        $this->useragent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.86 Safari/537.36';
    }

    public function debug($bool)
    {
        $this->debug = $bool;
    }

    public function responseHeaders($enable)
    {
        $this->showRequestHeaders = $enable;
    }

    /**
     * @param $path
     * @throws \Exception
     */
    public function setCacert($path)
    {
        if (file_exists($path)) {
            $this->cacert = $path;
        } else {
            throw new \Exception("File Not found with $path.");
        }
    }

    public function requestHeaders($enable)
    {
        $this->showResponseHeaders = $enable;
    }

    public function setUserAgent($ua)
    {
        $this->useragent = $ua;
    }

    public function setReferer($referer)
    {
        $this->referer = $referer;
        curl_setopt($this->ch, CURLOPT_REFERER, $this->referer);
    }

    /**
     * @param $url
     * @param $options
     * @throws \Exception
     */
    private function prepare($url, $options)
    {
        curl_close($this->ch);

        $this->ch = curl_init();

        if (!$url) {
            throw new \Exception('The url is not provided');
        }
        $this->url = $url;
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->useragent);
        if ($options['test']) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        if ($this->debug) {
            curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
        }

        if ($this->showRequestHeaders) {
            curl_setopt($this->ch, CURLINFO_HEADER_OUT, 1);
        }

        if ($this->showResponseHeaders) {
            curl_setopt($this->ch, CURLOPT_HEADER, 1);
        }

        if (isset($options['headers'])) {
            $this->headers = $options['headers'];
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $options['headers']);
        }

        if (isset($options['referer'])) {
            curl_setopt($this->ch, CURLOPT_REFERER, $options['referer']);
        }
    }

    /**
     * @return bool|string
     * @throws CurlException
     */
    private function execute()
    {
        $tuData = curl_exec($this->ch);
        $this->error = 'Error Number ' . curl_errno($this->ch) . ' with Message ' . curl_error($this->ch);
        $this->responseCode = curl_getinfo($this->ch);
        $this->responseCode = $this->responseCode['http_code'];

        if (!$tuData) {
            throw new CurlException(curl_error($this->ch), $this);
        }

        if ($error_no = curl_errno($this->ch)) {
            $error_message = curl_error($this->ch);
            if ($error_no === 60) {
                throw new CurlException("Something went wrong. cURL raised an error with number: $error_no and message: $error_message. " .
                    'Please check http://stackoverflow.com/a/21114601/846892 for a fix.' . PHP_EOL, $this);
            }

            throw new CurlException("Something went wrong. cURL raised an error with number: $error_no and message: $error_message." . PHP_EOL, $this);
        }

        return $tuData;
    }

    /**
     * @param $url
     * @param array $options
     * @return bool|string
     * @throws CurlException
     * @throws \Exception
     */
    public function get($url, $options = [])
    {
        $this->url = '';
        $this->requestMethod = '';
        $this->data = '';
        $this->headers = '';

        $this->prepare($url, $options);
        $this->requestMethod = 'GET';

        return $this->execute();
    }

    /**
     * @param $url
     * @param $data
     * @param array $options
     * @return bool|string
     * @throws CurlException
     * @throws \Exception
     */
    public function post($url, $data, $options = [])
    {
        $this->url = '';
        $this->requestMethod = '';
        $this->data = '';
        $this->headers = '';

        $data_string = json_encode($data);
        $this->data = $data_string;
        $this->requestMethod = 'POST';
        $this->prepare($url, $options);

        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string)]);

        return $this->execute();
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "Requesting  '$this->url' url using  '$this->requestMethod' method" . PHP_EOL .
            'and Data:' . print_R($this->data, true) . PHP_EOL .
            'Headers are : ' . print_r($this->headers, true) . PHP_EOL .
            'ErrorMessage(if any) :' . $this->error . PHP_EOL .
            'with Response Code:' . $this->responseCode;
    }
}
