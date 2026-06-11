<?php ?>
<main>
    <section class="section">
        <div class="container">
            <article class="card">
                <div class="card-body">
                    <h1>
                        <?= htmlspecialchars($post['title']) ?>
                    </h1>

                    <p class="text-muted">
                        <?= htmlspecialchars($post['created_at']) ?>
                    </p>

                    <div class="blog-content">
                        <?= nl2br(htmlspecialchars($post['content'])) ?>
                    </div>

                    <br>

                    <a href="/blog" class="btn-primary">← Back to Blog</a>
                </div>
            </article>
        </div>
    </section>
</main>