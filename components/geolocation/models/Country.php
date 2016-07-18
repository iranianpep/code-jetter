<?php

namespace CodeJetter\components\geolocation\models;

class Country
{
    private $name;
    private $code;

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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param $code
     *
     * @throws \Exception
     */
    public function setCode($code)
    {
        if (strlen($code) !== 2) {
            throw new \Exception("Country code must be 2 letters. '{$code}' is no valid");
        }

        $this->code = $code;
    }
}
