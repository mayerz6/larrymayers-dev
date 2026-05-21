<?php
$technologiesText = implode("\n", array_map(static fn ($technology) => $technology['technology'], $technologies ?? []));
?>

<main>
    <section class="section" id="project-edit">
        <div class="container">
            <p><a href="/projects/manage">&larr; Back to Project Management</a></p>
            <h1>Edit Project</h1>
            <p class="muted">Update the selected project and its technology list.</p>

            <form class="form card-body" method="POST" action="/projects/update">
                <input type="hidden" name="id" value="<?php echo (int) $project['id']; ?>">

                <div class="form-row">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($project['title']); ?>" required>
                </div>

                <div class="form-row">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($project['description']); ?></textarea>
                </div>

                <div class="form-row">
                    <label for="link">Link</label>
                    <input type="url" id="link" name="link" value="<?php echo htmlspecialchars($project['link']); ?>">
                </div>

                <div class="form-row">
                    <label for="technologies">Technologies Used</label>
                    <textarea id="technologies" name="technologies" rows="7" placeholder="Enter one item per line"><?php echo htmlspecialchars($technologiesText); ?></textarea>
                </div>

                <div class="hero-actions">
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                    <a class="btn btn-secondary" href="/projects/manage">Cancel</a>
                </div>
            </form>
        </div>
    </section>
</main>
