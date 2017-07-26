<?php

namespace App\Helpers;

/**
 * NetCommon$Json_Response_Model.
 */
class JsonResponse
{
    private $response = array();
    const JSON_KEY_STATUS = 'status';
    const JSON_KEY_MESSAGE = 'message';

    const JSON_VALUE_STATUS_SUCCESS = 'true';
    const JSON_VALUE_STATUS_FAIL = 'false';
    const JSON_VALUE_STATUS_ERROR = 'error';

    const FAILURE_STATUS_JSON_RESPONSE = '{"status":"error","message":""}';

    public function __construct()
    {
        $this->_response[self::JSON_KEY_STATUS] = self::JSON_VALUE_STATUS_ERROR;
        $this->_response[self::JSON_KEY_MESSAGE] = '';
    }

    /**
     * Sets the Json response to contain a success structure.
     *
     * @param string $message
     */
    public function setSuccess($message = null)
    {
        $this->_response[self::JSON_KEY_STATUS] = self::JSON_VALUE_STATUS_SUCCESS;

        if ($message) {
            $this->setMessage($message);
        }
    }

    /**
     * Sets the Json response to contain a failure structure.
     *
     * @param string $message
     */
    public function setFailure($message = null)
    {
        $this->_response[self::JSON_KEY_STATUS] = self::JSON_VALUE_STATUS_FAIL;

        if ($message) {
            $this->setMessage($message);
        }
    }

    /**
     * Sets the status of the response to self::JSON_KEY_STATUS_FAIL.
     */
    public function setError($message = null)
    {
        $this->setValue(self::JSON_KEY_STATUS, self::JSON_VALUE_STATUS_ERROR);

        if ($message) {
            $this->setMessage($message);
        }
    }

    /**
     * @param string $message
     */
    public function setMessage($message = '')
    {
        $this->setValue(self::JSON_KEY_MESSAGE, $message);
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setCustomEntry($key, $value)
    {
        $this->_response[$key] = $value;
    }

    /**
     * Allows updating existing custom or internal keys.
     * If a given key is missing - it will be created with the given value.
     *
     * @param string $key
     * @param string $value
     */
    public function setValue($key, $value)
    {
        if (!isset($this->_response[$key])) {
            $this->setCustomEntry($key, $value);
        } else {
            $this->_response[$key] = $value;
        }
    }

    /**
     * @return jsonzied string
     *                  In any exception being throws while encoding, we will return a static structure of JSON with fail status
     */
    public function __toString()
    {
        try {
            return json_encode($this->_response);
        } catch (Exception $e) {
            return $self::FAILURE_STATUS_JSON_RESPONSE;
        }
    }
}
