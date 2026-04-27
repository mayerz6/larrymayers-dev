<?php ?>


        <main>
            

            <?php if (empty($resumes)): ?>
                <div class="container">
                    <p>No resume entries found.</p>
                </div>
            <?php else: ?>
            <div class="card-body">
                <h3>Existing Resume Entries</h3>
                <ol class="resume-list">
                    <?php foreach ($resumes as $resume): ?>
                        <li class="resume-item" datat-id="<?= $resume['id'] ?>">
                            <h4><?php echo htmlspecialchars($resume['title']); ?> at <?php echo htmlspecialchars($resume['company']); ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($resume['summary'])); ?></p>
                            <p><strong>Duration:</strong> <?php echo htmlspecialchars($resume['start_year']); ?> - <?php echo htmlspecialchars($resume['end_year']); ?></p>
                             <p><strong>Duties:</strong>
                            <ul>
                                <?php foreach ($duties[$resume['id']] ?? [] as $duty): ?>
                                    <li><?php echo htmlspecialchars($duty['duty']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            </p>
                            <hr>
                            <button class="edit-resume-btn" data-id="<?= $resume['id'] ?>">Edit</button>
                            <button class="delete-resume-btn" data-id="<?= $resume['id'] ?>">Delete</button>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <?php endif; ?>

        </main>

