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

    public static function createProjectsTable(PDO $conn): void {
        $sql = "CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT NOT NULL,
            link TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        try {
            $conn->exec($sql);
        } catch (PDOException $e) {
            die(json_encode(["message" => "Failed to create projects table: " . $e->getMessage()]));
        }
    }   

    public static function createExpertiseTable(PDO $conn): void {
        $sql = "CREATE TABLE IF NOT EXISTS expertise (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            skill TEXT NOT NULL,
            level TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        try {
            $conn->exec($sql);
        } catch (PDOException $e) {
            die(json_encode(["message" => "Failed to create expertise table: " . $e->getMessage()]));
        }
    }

    public static function createResumeTable(PDO $conn): void {
        $sql = "CREATE TABLE IF NOT EXISTS resume (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            company TEXT NOT NULL,
            summary TEXT NOT NULL,
            start_year TEXT NOT NULL,
            end_year TEXT,
            order_index INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        try {
            $conn->exec($sql);
        } catch (PDOException $e) {
            die(json_encode(["message" => "Failed to create resume table: " . $e->getMessage()]));
        }
    }

    public static function createDutiesTable(PDO $conn): void {
        $sql = "CREATE TABLE IF NOT EXISTS duties (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            resume_id INTEGER NOT NULL,
            duty TEXT NOT NULL,
            order_index INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (resume_id) REFERENCES resume(id) ON DELETE CASCADE
        )";
        try {
            $conn->exec($sql);
        } catch (PDOException $e) {
            die(json_encode(["message" => "Failed to create duties table: " . $e->getMessage()]));
        }
    }

    public static function createQualificationsTable(PDO $conn): void {
        $sql = "CREATE TABLE IF NOT EXISTS qualifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            institution TEXT NOT NULL,
            year TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        try {
            $conn->exec($sql);
        } catch (PDOException $e) {
            die(json_encode(["message" => "Failed to create qualifications table: " . $e->getMessage()]));
        }
    }

    public static function createUsersTable(PDO $conn): void {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        try {
            $conn->exec($sql);
        } catch (PDOException $e) {
            die(json_encode(["message" => "Failed to create users table: " . $e->getMessage()]));
        }
    }

    public static function insertDefaultUser(PDO $conn): void {
        $email = "larry@larrymayers.site";
        $password_hash = password_hash("M@y3rZ!2#", PASSWORD_DEFAULT);
        $sql = "INSERT OR IGNORE INTO users (email, password_hash) VALUES (:email, :password_hash)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password_hash", $password_hash);
        $stmt->execute();
    }

    public static function closeConnection(): void {
        self::$conn = null;
    }

}