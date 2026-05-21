<?php ?>

<main>
    <section class="section" id="projects">
        <div class="container">
            <p class="eyebrow">Portfolio</p>
            <h1>Projects Catalog</h1>
            <p class="lead">Selected technical work, systems, and product builds.</p>

            <?php if (empty($projects)): ?>
                <div class="card-body">
                    <p>No projects found.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-2">
                    <?php foreach ($projects as $project): ?>
                        <article class="card card-body">
                            <h2><?php echo htmlspecialchars($project['title']); ?></h2>
                            <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>

                            <?php if (!empty($technologies[$project['id']])): ?>
                                <div class="tag-list">
                                    <?php foreach ($technologies[$project['id']] as $technology): ?>
                                        <span class="tag"><?php echo htmlspecialchars($technology['technology']); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($project['link'])): ?>
                                <div class="hero-actions">
                                    <a class="btn btn-secondary" href="<?php echo htmlspecialchars($project['link']); ?>" target="_blank" rel="noopener noreferrer">View Project</a>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
