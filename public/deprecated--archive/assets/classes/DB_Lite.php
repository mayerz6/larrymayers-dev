<?php

class DB_Lite {
    static function connect(): PDO {
        $pdo = new PDO('sqlite:' . './assets/databases/larrymayers_contact.sqlite');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
        // echo "./../assets/databases/larrymayers_contact.sqlite";
        // die();
    }

    static function loadSchema(PDO $pdo, string $schemaFile): void {
        $sql = file_get_contents($schemaFile);
        if(false === $sql){
            die("SQL execution failed!!!");
        }
        $pdo->exec($sql);
    }

    static function insertMessage(PDO $pdo, array $data): bool {
        try {
            $sql = "INSERT INTO messages ";
            $sql .= "(email, topic, message) VALUES ";
            $sql .= "(:email, :topic, :message)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":email" => $data["email"],
                ":topic" => $data["topic"],
                ":message" => $data["message"]
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Insert failed: " . $e->getMessage());
            return false;
        }
    }


}