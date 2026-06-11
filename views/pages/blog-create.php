<?php ?>

<main>
    <section class="section" id="blog-post-create">
        <div class="container">
            <p><a href="/blog/manage">&larr; Back to blog manager</a></p>
            <h1>Add Blog Post</h1>
            <p class="lead">Technical articles, project deep-dives, and industry commentary.</p>

            <?php if (!empty($flash)): ?>
            <p class="surface card-body"><?php echo htmlspecialchars($flash); ?></p>
            <?php endif; ?>
            <form id="blogPostForm" class="form card-body" method="POST" action="/blog/create">
                <fieldset>
                    <legend>Blog Post Details</legend>
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title">
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="7"></textarea>
                    </div>
                    <p id="blogResMsg" aria-live="polite"></p>
                    <button type="submit" class="btn btn-primary">Create Post</button>
                </fieldset>
            </form>
        </div>
    </section>
</main>