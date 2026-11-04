<?php ?>

<main>
    <section class="section" id="blog">
        <div class="container">
            <h1>Latest Events</h1>
            <p class="lead">News, updates, and insights from the world of web development and technology.</p>
            <?php if (empty($posts)): ?>
            <div class="card-body">
                <p>No blog posts found.</p>
            </div>
            <?php else: ?>
            <div class="grid grid-2">
                <?php foreach ($posts as $post): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title"><?= htmlspecialchars($post['title']) ?></h3>
                            <p class="card-text"><?= htmlspecialchars($post['content']) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>