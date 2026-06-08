<?php


class Database
{

    private static $host;
    private static $db;
    private static $user;
    private static $pwd;
    private static $charset = "utf8mb4";
    // public $pdo;


    public function __construct()
    {

        // self::$host = Secrets::getStageHost();
        // self::$user = Secrets::getDBUser();
        // self::$pwd = Secrets::getPwdStage();
        // self::$db = Secrets::getDBStage();


    }

    public static ?PDO $conn = null;

    public static function getLiteConnection(): PDO
    {
        if (self::$conn === null) {
            try {
                self::$conn = new PDO("sqlite:" . __DIR__ . "/../../database/app.sqlite");
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->exec("PRAGMA foreign_keys = ON");
            } catch (PDOException $e) {
                die(json_encode(["message" => "SQLite connection failed: " . $e->getMessage()]));
            }
        }
        return self::$conn;
    }


    public function getConnection()
    {

        $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=" . self::$charset;
        // $dsn = "mysql:host=self::$host;dbname=$this->db;charset=$this->charset";

        try {
            $pdo = new PDO($dsn, self::$user, self::$pwd);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die(json_encode(["message" => "Database connection failed: " . $e->getMessage()]));
        }

    }

    public static function createMsgTable(PDO $conn): void
    {
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

    public static function createProjectsTable(PDO $conn): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT NOT NULL,
            link TEXT,
            order_index INTEGER NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        try {
            $conn->exec($sql);
            self::ensureColumn($conn, 'projects', 'order_index', 'INTEGER NOT NULL DEFAULT 0');
        } catch (PDOException $e) {
            die(json_encode(["message" => "Failed to create projects table: " . $e->getMessage()]));
        }
    }

    public static function createBlogPostsTable(PDO $conn): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS blog_posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        try {
            $conn->exec($sql);
        } catch (PDOException $e) {
            die(json_encode(["message" => "Failed to create blog_posts table: " . $e->getMessage()]));
        }
    }

    public static function createBlogTagsTable(PDO $conn): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS blog_tags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            post_id INTEGER NOT NULL,
            tag TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
        )";
        try {
            $conn->exec($sql);
        } catch (PDOException $e) {
            die(json_encode(["message" => "Failed to create blog_tags table: " . $e->getMessage()]));
        }
    }


    public static function createExpertiseTable(PDO $conn): void
    {
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

    public static function createProjectDutiesTable(PDO $conn): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS project_duties (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id INTEGER NOT NULL,
            duty TEXT NOT NULL,
            order_index INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
        )";
        try {
            $conn->exec($sql);
        } catch (PDOException $e) {
            die(json_encode(["message" => "Failed to create project_duties table: " . $e->getMessage()]));
        }
    }

    public static function createProjectTechnologiesTable(PDO $conn): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS project_technologies (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id INTEGER NOT NULL,
            technology TEXT NOT NULL,
            order_index INTEGER NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
        )";
        try {
            $conn->exec($sql);
            self::ensureColumn($conn, 'project_technologies', 'order_index', 'INTEGER NOT NULL DEFAULT 0');
        } catch (PDOException $e) {
            die(json_encode(["message" => "Failed to create project_technologies table: " . $e->getMessage()]));
        }
    }


    public static function clearProjectsTable(PDO $conn): void
    {
        $conn->exec("DELETE FROM project_duties");
        $conn->exec("DELETE FROM project_technologies");
        $conn->exec("DELETE FROM projects");
    }

    private static function ensureColumn(PDO $conn, string $table, string $column, string $definition): void
    {
        $stmt = $conn->query("PRAGMA table_info({$table})");
        $columns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');

        if (!in_array($column, $columns, true)) {
            $conn->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
        }
    }


    public static function createQualificationsTable(PDO $conn): void
    {
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

    public static function createResumeTable(PDO $conn): void
    {
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

    public static function createDutiesTable(PDO $conn): void
    {
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

    public static function createUsersTable(PDO $conn): void
    {
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

    public static function insertDefaultUser(PDO $conn): void
    {
        $email = "larry@larrymayers.site";
        $password_hash = password_hash("M@y3rZ!2#", PASSWORD_DEFAULT);
        $sql = "INSERT OR IGNORE INTO users (email, password_hash) VALUES (:email, :password_hash)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password_hash", $password_hash);
        $stmt->execute();
    }

    public static function closeConnection(): void
    {
        self::$conn = null;
    }

}