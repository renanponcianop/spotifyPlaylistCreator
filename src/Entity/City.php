<?php

namespace App\Entity;

class City
{
    private $city;

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }
}
