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
