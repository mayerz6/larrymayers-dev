<?php ?>

<main>
    <section class="section" id="resume-manage">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Admin</p>
                <h1>Manage Resume Entries</h1>
                <p class="lead">Create, edit, or remove the resume entries shown on the public portfolio.</p>
            </div>

            <?php if (!empty($flash)): ?>
                <p class="surface card-body"><?php echo htmlspecialchars($flash); ?></p>
            <?php endif; ?>

            <div class="hero-actions">
                <a class="btn btn-primary" href="/resume/create">Add Entry</a>
                <a class="btn btn-secondary" href="/resume">View Public Resume</a>
            </div>

            <?php if (empty($resumes)): ?>
                <div class="surface card-body">
                    <p>No resume entries found.</p>
                </div>
            <?php else: ?>
                <ol class="resume-list grid">
                    <?php foreach ($resumes as $resume): ?>
                        <li class="resume-item surface card-body" data-id="<?php echo (int) $resume['id']; ?>">
                            <h3>
                                <?php echo htmlspecialchars($resume['title']); ?>
                                <span class="muted">at <?php echo htmlspecialchars($resume['company']); ?></span>
                            </h3>

                            <p class="muted">
                                <?php echo htmlspecialchars($resume['start_year']); ?>
                                -
                                <?php echo htmlspecialchars($resume['end_year'] ?: 'Present'); ?>
                            </p>

                            <?php if (!empty($resume['summary'])): ?>
                                <p><?php echo nl2br(htmlspecialchars($resume['summary'])); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($duties[$resume['id']])): ?>
                                <ul>
                                    <?php foreach ($duties[$resume['id']] as $duty): ?>
                                        <li><?php echo htmlspecialchars($duty['duty']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <div class="hero-actions">
                                <a class="btn btn-secondary" href="/resume/edit?id=<?php echo (int) $resume['id']; ?>">Edit</a>
                                <form method="POST" action="/resume/delete">
                                    <input type="hidden" name="id" value="<?php echo (int) $resume['id']; ?>">
                                    <button class="btn btn-secondary delete-resume-btn" type="submit" data-id="<?php echo (int) $resume['id']; ?>">Delete</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>
    </section>
</main>
