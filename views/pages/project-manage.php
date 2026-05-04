<?php ?>

<main>
    <h1>Project Management Dashboard | Secure Access Only</h1>
    <?php if(!empty($flash)) : ?>   
        <div class="alert alert-info">
            <?php echo htmlspecialchars($flash); ?>
        </div>
    <?php endif; ?>
    <section id="projectList">
        <div class="container">
            <h2><em>Manage Projects Catalog</em></h2>
            <p class="text-muted">
                <!-- Add any additional information or instructions here -->
            </p>

            <?php if(!empty($projects) && !empty($duties)): ?>


                <?php foreach ($projects as $project): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h4>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                            <p><strong>Duration:</strong> <?php echo htmlspecialchars($project['start_year']); ?> - <?php echo htmlspecialchars($project['end_year']); ?></p>
                            <p><strong>Duties:</strong>
                                <ul>
                                    <?php foreach ($duties[$project['id']] ?? [] as $duty): ?>
                                        <li><?php echo htmlspecialchars($duty['duty']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No projects found.</p>
            <?php endif; ?> 
        </div>
    </section>
</main>



