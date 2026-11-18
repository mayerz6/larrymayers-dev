<?php ?>

<main>
    <section class="section" id="resumeList">
        <div class="container">
            <h1>Resume Catalog</h1>
            <p class="lead">Selected work experience and professional history. Click a role to expand it.</p>

            <?php if (empty($resumes)): ?>
                <div class="card-body">
                    <p>No resume entries found.</p>
                </div>
            <?php else: ?>
                <div class="resume-feed">
                    <?php foreach ($resumes as $resume): ?>
                        <?php $positionDuties = $duties[$resume['id']] ?? []; ?>
                        <details class="resume-entry">
                            <summary>
                                <span class="resume-role">
                                    <strong><?php echo htmlspecialchars($resume['title']); ?></strong>
                                    <span class="resume-company">at <?php echo htmlspecialchars($resume['company']); ?></span>
                                </span>
                                <span class="resume-years">
                                    <?php echo htmlspecialchars($resume['start_year']); ?>
                                    &ndash;
                                    <?php echo htmlspecialchars($resume['end_year'] ?: 'Present'); ?>
                                </span>
                                <span class="resume-chevron" aria-hidden="true"></span>
                            </summary>
                            <div class="resume-entry-body">
                                <div class="resume-entry-body-inner">
                                    <?php if (!empty($resume['summary'])): ?>
                                        <p><?php echo nl2br(htmlspecialchars($resume['summary'])); ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($positionDuties)): ?>
                                        <ul class="resume-duties">
                                            <?php foreach ($positionDuties as $duty): ?>
                                                <li><?php echo htmlspecialchars($duty['duty']); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
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
