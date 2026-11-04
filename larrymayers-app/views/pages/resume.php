<?php ?>

<main>
    <section class="section" id="resumeList">
        <div class="container">
            <h1>Resume Catalog</h1>
            <p class="lead">Selected work experience and professional history.</p>

            <?php if (empty($resumes)): ?>
                <div class="card-body">
                    <p>No resume entries found.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-2">
                    <?php foreach ($resumes as $resume): ?>
                        <article class="card card-body">
                            <h3>
                                <?php echo htmlspecialchars($resume['title']); ?>
                                at
                                <?php echo htmlspecialchars($resume['company']); ?>
                            </h3>

                            <p class="muted">
                                <?php echo htmlspecialchars($resume['start_year']); ?>
                                -
                                <?php echo htmlspecialchars($resume['end_year'] ?: 'Present'); ?>
                            </p>

                            <?php if (!empty($resume['summary'])): ?>
                                <p><?php echo nl2br(htmlspecialchars($resume['summary'])); ?></p>
                            <?php endif; ?>

                           
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
