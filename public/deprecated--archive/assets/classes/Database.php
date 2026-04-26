<?php

require_once "Secrets.php";

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// header('Content-Type: application/json'); // important!



class Database{

    private static $host;
    private static $db;
    private static $user;
    private static $pwd;
    private static $charset = "utf8mb4";
    // public $pdo;

    
    public function __construct(){

        self::$host = Secrets::getStageHost();
        self::$user = Secrets::getDBUser();
        self::$pwd = Secrets::getPwdStage();
        self::$db = Secrets::getDBStage();
        
        
    }

    public function getConnection(){

        $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=" . self::$charset;
        // $dsn = "mysql:host=self::$host;dbname=$this->db;charset=$this->charset";
        
        try{
            $pdo = new PDO($dsn, self::$user, self::$pwd);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e){
            die(json_encode(["message" => "Database connection failed: " . $e->getMessage()]));
        }

    }

}