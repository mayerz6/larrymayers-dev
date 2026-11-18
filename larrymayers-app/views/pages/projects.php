<?php ?>

<main>
    <section class="section" id="projects">
        <div class="container">

            <h1>Projects Catalog</h1>
            <p class="lead"> <b><small>My professional showcase</small></b> | Selected technical work, system deployments, &amp; product builds. Click a project to expand it.</p>

            <?php if (empty($projects)): ?>
                <div class="card-body">
                    <p>No projects found.</p>
                </div>
            <?php else: ?>
                <div class="project-feed">
                    <?php foreach ($projects as $project): ?>
                        <?php $projectTechnologies = $technologies[$project['id']] ?? []; ?>
                        <details class="project-entry">
                            <summary>
                                <span class="project-name">
                                    <strong><?php echo htmlspecialchars($project['title']); ?></strong>
                                </span>
                                <?php if (!empty($projectTechnologies)): ?>
                                    <span class="project-tech-count">
                                        <?php echo count($projectTechnologies); ?>
                                        tech<?php echo count($projectTechnologies) === 1 ? '' : 's'; ?>
                                    </span>
                                <?php endif; ?>
                                <span class="project-chevron" aria-hidden="true"></span>
                            </summary>
                            <div class="project-entry-body">
                                <div class="project-entry-body-inner">
                                    <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>

                                    <?php if (!empty($projectTechnologies)): ?>
                                        <div class="tag-list">
                                            <?php foreach ($projectTechnologies as $technology): ?>
                                                <span class="tag"><?php echo htmlspecialchars($technology['technology']); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($project['link'])): ?>
                                        <div class="hero-actions">
                                            <a class="btn btn-secondary" href="<?php echo htmlspecialchars($project['link']); ?>" target="_blank" rel="noopener noreferrer">View Project</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
