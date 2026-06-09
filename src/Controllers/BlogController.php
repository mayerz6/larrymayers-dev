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
        SELECT id, title, content, created_at
        FROM blog_posts
        ORDER BY created_at DESC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchBlogTagsMap(PDO $db): array
{
    $stmt = $db->query("
        SELECT id, post_id, tag, created_at
        FROM blog_tags
        ORDER BY post_id ASC, created_at ASC
    ");

    $tagsMap = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $tag) {
        $tagsMap[$tag['post_id']][] = $tag;
    }

    return $tagsMap;
}

function fetchBlogPost(PDO $db, int $id): ?array
{
    $stmt = $db->prepare("
        SELECT id, title, content, created_at
        FROM blog_posts
        WHERE id = :id
    ");
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    return $post ?: null;
}

function blogCreateForm(): void
{
    requireAuth();
    view('blog-create');
}


function blogCreate(): void
{
    requireAuth();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Title and content are required.',
            ], 400);
            return;
        }

        $_SESSION['flash'] = 'Title and content are required.';
        redirectTo('/blog/create');
    }

    $db = blogDb();

    try {
        $stmt = $db->prepare("
            INSERT INTO blog_posts (title, content)
            VALUES (:title, :content)
        ");
        $stmt->execute([
            ':title' => $title,
            ':content' => $content,
        ]);

        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'success',
                'message' => 'Blog post created successfully.',
                'redirect' => '/blog/manage',
            ]);
            return;
        }

        $_SESSION['flash'] = 'Blog post created successfully.';
        redirectTo('/blog/manage');
    } catch (Throwable $e) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Unable to create blog post.',
            ], 500);
            return;
        }

        $_SESSION['flash'] = 'Unable to create blog post.';
        redirectTo('/blog/create');
    }
}

function blogEditForm(): void
{
    requireAuth();

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        $_SESSION['flash'] = 'Missing blog post ID.';
        redirectTo('/blog/manage');
    }

    $db = blogDb();
    $post = fetchBlogPost($db, $id);

    if (!$post) {
        $_SESSION['flash'] = 'Blog post not found.';
        redirectTo('/blog/manage');
    }

    view('blog-edit', [
        'post' => $post,
    ]);
}

function blogUpdate(): void
{
    requireAuth();

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (!$id || $title === '' || $content === '') {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Blog post ID, title, and content are required.',
            ], 400);
            return;
        }

        $_SESSION['flash'] = 'Blog post ID, title, and content are required.';
        redirectTo('/blog/manage');
    }

    $db = blogDb();

    try {
        $stmt = $db->prepare("
            UPDATE blog_posts
            SET title = :title,
                content = :content
            WHERE id = :id
        ");
        $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':id' => $id,
        ]);

        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'success',
                'message' => 'Blog post updated successfully.',
                'redirect' => '/blog/manage',
            ]);
            return;
        }

        $_SESSION['flash'] = 'Blog post updated successfully.';
        redirectTo('/blog/manage');
    } catch (Throwable $e) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Unable to update blog post.',
            ], 500);
            return;
        }

        $_SESSION['flash'] = 'Unable to update blog post.';
        redirectTo("/blog/edit?id={$id}");
    }
}


function blogManage(): void
{
    requireAuth();

    $db = blogDb();
    view('blog-manage', [
        'posts' => fetchBlogPosts($db),
        'tags' => fetchBlogTagsMap($db),
        'flash' => $_SESSION['flash'] ?? null,
    ]);

    unset($_SESSION['flash']);
}

function blog(): void
{
    $db = blogDb();

    view('blog', [
        'posts' => fetchBlogPosts($db),
        'tags' => fetchBlogTagsMap($db),
    ]);
}
