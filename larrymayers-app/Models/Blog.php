<?php

require_once '../core/Database.php';

/**
 * Fetches the most recent blog posts.
 *
 * @param int $limit Number of posts to return
 * @return array<int, array{title: string, body: string}>
 */
class Blog {
    private $db;

    public function __construct(){
        $this->db = (new Database())->getConnection();
    }
    
    public function fetch(int $limit): array {
        $stmt = $this->db->prepare("SELECT title, content, author, date_created FROM blog_posts ORDER BY date_created DESC LIMIT :lim");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}