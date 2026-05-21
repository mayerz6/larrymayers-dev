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
            <p><a href="/projects/create" class="btn btn-primary mb-3">+ Add New Project</a></p>
            <?php if(!empty($projects)): ?>


                <?php foreach ($projects as $project): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="card-title"><u>Title</u>: <?php echo htmlspecialchars($project['title']); ?></h4>
                            <p class="card-text"><u>Breakdown</u>: <?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                            <?php if (!empty($technologies[$project['id']])): ?>
                                <p><u>Technologies</u>:</p>
                                <ul>
                                    <?php foreach ($technologies[$project['id']] as $technology): ?>
                                        <li><?php echo htmlspecialchars($technology['technology']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if (!empty($project['link'])): ?>
                                <p><a href="<?php echo htmlspecialchars($project['link']); ?>" target="_blank" rel="noopener noreferrer">View Project</a></p>
                            <?php endif; ?>
                            <p><a class="btn btn-secondary" href="/projects/edit?id=<?php echo (int) $project['id']; ?>">Edit</a></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No projects found.</p>
            <?php endif; ?> 
        </div>
    </section>
</main>



