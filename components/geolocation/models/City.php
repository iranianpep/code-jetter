<?php

namespace CodeJetter\components\geolocation\models;

use CodeJetter\core\BaseModel;

class City extends BaseModel
{
    private $name;
    private $stateId;
    private $countryCode;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getStateId()
    {
        return $this->stateId;
    }

    /**
     * @param $stateId
     */
    public function setStateId($stateId)
    {
        $this->stateId = $stateId;
    }

    /**
     * @return Country
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param $countryCode
     *
     * @throws \Exception
     */
    public function setCountryCode($countryCode)
    {
        if (strlen($countryCode) !== 2) {
            throw new \Exception("Country code must be 2 letters. '{$countryCode}' is no valid");
        }

        $this->countryCode = $countryCode;
    }
}
