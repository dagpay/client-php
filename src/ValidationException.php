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
 * ValidationException
 * - used to generate the exception related to validation which raised when response
 *   from instamojo server is not as desired.
 *   used to throw the Validation errors at the time of creating order.
 *     used to throw the authentication failed errors.
 */
class ValidationException extends \Exception
{
    private $errors;
    private $apiResponse;

    public function __construct($message, $errors, $apiResponse)
    {
        parent::__construct($message, 0);
        $this->errors = $errors;
        $this->apiResponse = $apiResponse;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getResponse()
    {
        return $this->apiResponse;
    }
}
