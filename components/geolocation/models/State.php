<?php

namespace CodeJetter\components\geolocation\models;

class State
{
    private $name;
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
}
