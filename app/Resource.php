<?php

namespace App;

class Resource
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public static function get($resource){
        return new Resource($resource);
    }

    public function getName() {
        return $this->name;
    }
}
