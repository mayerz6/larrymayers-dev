<?php


function blogDb(): PDO
{
    $db = Database::getLiteConnection();
    Database::createBlogPostsTable($db);
    Database::createBlogTagsTable($db);
    return $db;
}

function fetchBlogPosts(PDO $db): array
{
    $stmt = $db->query("
        SELECT * FROM blog_posts
        ORDER BY created_at DESC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchBlogTagsMap(PDO $db): array
{
    $stmt = $db->query("
        SELECT id, post_id, tag, order_index
        FROM blog_tags
        ORDER BY post_id ASC, order_index ASC
    ");

    $tagsMap = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $tag) {
        $tagsMap[$tag['post_id']][] = $tag;
    }

    return $tagsMap;
}

function blog(): void
{
    $db = blogDb();

    view('blog', [
        'posts' => fetchBlogPosts($db),
        'tags' => fetchBlogTagsMap($db),
    ]);
}