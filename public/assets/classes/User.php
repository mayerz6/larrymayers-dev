
<?php

class User {

    private static $email = "info@larrymayers.site";
    private static $pwd = "M@y3rZ.S0urc#!9a";
    private static $host = "imap.titan.email";

    public static function getPwd(){ return self::$pwd; }
    public static function getEmail(){ return self::$email; }
    public static function getHost(){ return self::$host; }

}