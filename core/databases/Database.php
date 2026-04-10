<?php

// require_once "Secrets.php";

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

        // self::$host = Secrets::getStageHost();
        // self::$user = Secrets::getDBUser();
        // self::$pwd = Secrets::getPwdStage();
        // self::$db = Secrets::getDBStage();
        
        
    }

    public static ?PDO $conn = null;

    public static function getLiteConnection(): PDO {
        if (self::$conn === null) {
            try {
                self::$conn = new PDO("sqlite:" . __DIR__ . "/messages.sqlite");
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die(json_encode(["message" => "SQLite connection failed: " . $e->getMessage()]));
            }
        }
        return self::$conn;
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

    public static function createMsgTable(PDO $conn): void {
        $sql = "CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        try {
            $conn->exec($sql);
        } catch (PDOException $e) {
            die(json_encode(["message" => "Failed to create messages table: " . $e->getMessage()]));
        }
    }   
    
    public static function closeConnection(): void {
        self::$conn = null;
    }

}