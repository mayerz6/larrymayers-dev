<?php ?>

<main>
    <section class="section" id="blog">
        <div class="container">
            <p class="eyebrow">Insights</p>
            <h1>Blog Post Management Dashboard | Secure Access Only</h1>
            <p class="lead">Technical articles, project deep-dives, and industry commentary.</p>
            <?php if (!empty($flash)): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($flash); ?>
            </div>
            <?php endif; ?>

            <h2>Manage Blog Posts Catalog</h2>
            <p><a href="/blog/create" class="btn btn-primary mb-3">+ Add New Blog Post</a></p>
            <?php if (empty($posts)): ?>
            <p>No blog posts available yet. Check back soon!</p>
            <?php else: ?>
            <div class="grid grid-2">
                <?php foreach ($posts as $post): ?>
                <article class="card card-body">
                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                    <p class="text-small"><?php echo htmlspecialchars($post['summary']); ?></p>
                    <p>
                        <a class="btn btn-info" href="/blog/edit?id=<?php echo (int) $post['id']; ?>">Edit</a>
                    </p>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>