<?php

class Credential {

    private $name;
    private $achieve_date;
    private $expire_date;
    private $vendor;

    public function __construct($name, $achieve_date, $expire_date, $vendor){
        $this->name = $name;
        $this->achieve_date = $achieve_date;
        $this->expire_date = $expire_date;
        $this->vendor = $vendor;
    }
}