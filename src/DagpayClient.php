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

class DagpayClient
{
    private $curl;
    private $environment_id;
    private $user_id;
    private $secret;
    private $test;
    private $platform;

    public function __construct($environment_id, $user_id, $secret, $mode, $platform = 'standalone')
    {
        if ($platform === 'standalone') {
            $this->curl = new Curl();
        }

        $this->environment_id = $environment_id;
        $this->user_id = $user_id;
        $this->secret = $secret;
        $this->test = $mode;
        $this->platform = $platform;
    }

    /**
     * @param $length
     * @return string
     * @throws \Exception
     */
    private function get_random_string($length)
    {
        return strtoupper(
            bin2hex(
                random_bytes(
                    ceil($length / 2)
                )
            )
        );
    }

    private function get_signature($tokens)
    {
        return hash_hmac(
            'sha512',
            implode(':', $tokens),
            $this->secret
        );
    }

    private function get_create_invoice_signature($info)
    {
        return $this->get_signature([
            $info['currencyAmount'],
            $info['currency'],
            $info['description'],
            $info['data'],
            $info['userId'],
            $info['paymentId'],
            $info['date'],
            $info['nonce']
        ]);
    }

    public function get_invoice_info_signature($info)
    {
        return $this->get_signature([
            $info->id,
            $info->userId,
            $info->environmentId,
            $info->coinAmount,
            $info->currencyAmount,
            $info->currency,
            $info->description,
            $info->data,
            $info->paymentId,
            $info->qrCodeUrl,
            $info->paymentUrl,
            $info->state,
            $info->createdDate,
            $info->updatedDate,
            $info->expiryDate,
            $info->validForSeconds,
            $info->statusDelivered ? 'true' : 'false',
            $info->statusDeliveryAttempts,
            $info->statusLastAttemptDate !== null ? $info->statusLastAttemptDate : '',
            $info->statusDeliveredDate !== null ? $info->statusDeliveredDate : '',
            $info->date,
            $info->nonce
        ]);
    }

    /**
     * @param $id
     * @param $currency
     * @param $total
     * @return array|mixed|object
     * @throws \Exception
     */
    public function create_invoice($id, $currency, $total)
    {
        $datetime = new \DateTime();
        $invoice = [
            'userId' => $this->user_id,
            'environmentId' => $this->environment_id,
            'currencyAmount' => (float) $total,
            'currency' => $currency,
            'description' => 'Dagcoin Payment Gateway invoice',
            'data' => 'Order',
            'paymentId' => (string) $id,
            'date' => $datetime->format(\DateTime::ATOM),
            'nonce' => $this->get_random_string(32)
        ];

        $signature = $this->get_create_invoice_signature($invoice);
        $create_invoice_request_info = $invoice;
        $create_invoice_request_info['signature'] = $signature;

        return $this->make_request('POST', 'invoices', $create_invoice_request_info);
    }

    /**
     * @param $id
     * @return array|mixed|object
     * @throws \Exception
     */
    public function get_invoice_info($id)
    {
        return $this->make_request('GET', 'invoices/' . $id);
    }

    /**
     * @param $id
     * @return array|mixed|object
     * @throws \Exception
     */
    public function cancel_invoice($id)
    {
        $result = $this->make_request('POST', 'invoices/cancel', [
            'invoiceId' => $id
        ]);

        return $result;
    }

    /**
     * @param $method
     * @param $url
     * @param array $data
     * @return array|mixed|object
     * @throws CurlException
     * @throws \Exception
     */
    private function make_request($method, $url, $data = [])
    {
        if ($this->platform === 'standalone') {
            $options = ['test' => $this->test === 'yes'];
            // TODO: catch errors
            if ($method === 'POST') {
                return json_decode($this->curl->post($this->get_url() . $url, $data, $options), false);
            }
            if ($method === 'GET') {
                return json_decode($this->curl->get($this->get_url() . $url, $options), false);
            }
        } elseif ('wordpress') {
            $data = json_encode($data);
            $request['headers'] = ['Content-Type' => 'application/json'];
            $response = null;

            if ($method === 'POST') {
                $request['body'] = $data;
                $response = wp_safe_remote_post($this->get_url() . $url, $request);
            } elseif ($method === 'GET') {
                $response = wp_safe_remote_get($this->get_url() . $url, $request);
            }

            if (is_wp_error($response)) {
                throw new \Exception('Something failed! Please try again later...');
            }

            $data = json_decode(wp_remote_retrieve_body($response), false);
            if (!$data->success) {
                throw new \Exception($data->error ? $data->error : "Something went wrong! Please try again later or contact our support...");
            }

            return $data->payload;
        }
        return null;
    }

    private function get_url()
    {
        return $this->test ? 'https://test-api.dagpay.io/api/' : 'https://api.dagpay.io/api/';
    }
}
