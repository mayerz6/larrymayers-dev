<main>
    <section class="section" id="blog-post-edit">
        <div class="container">
            <p><a href="/blog/manage">&larr; Back to blog manager</a></p>
            <h1>Edit Blog Post</h1>
            <p class="muted">Update the selected article and publish the changes to the public blog page.</p>

            <form id="blogPostForm" class="form card-body" method="POST" action="/blog/update">
                <input type="hidden" name="id" value="<?php echo (int) $post['id']; ?>">

                <fieldset>
                    <legend>Blog Post Details</legend>
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input
                            type="text"
                            class="form-control"
                            id="title"
                            name="title"
                            value="<?php echo htmlspecialchars($post['title']); ?>"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                    </div>
                    <p id="blogResMsg" aria-live="polite"></p>
                    <div class="hero-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a class="btn btn-secondary" href="/blog/manage">Cancel</a>
                    </div>
                </fieldset>
            </form>
        </div>
    </section>
</main>
