<?php

namespace CodeJetter\components\geolocation\models;

use CodeJetter\core\BaseModel;

class State extends BaseModel
{
    private $name;
    private $abbr;
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

    /**
     * @return string
     */
    public function getAbbr()
    {
        return $this->abbr;
    }

    /**
     * @param string $abbr
     */
    public function setAbbr($abbr)
    {
        $this->abbr = $abbr;
    }
}
