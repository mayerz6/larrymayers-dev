<?php ?>

        <main>
            <section id="resumeList">
                <?php if (!empty($resumes) && !empty($duties)): ?>
                    <div class="container">
                        <h2>Resume/Projects Catalog</h2>
                        <ol class="resume-list">
                            <?php foreach ($resumes as $resume): ?>
                                <li class="resume-item">
                                    <h3><?php echo htmlspecialchars($resume['title']); ?> at <?php echo htmlspecialchars($resume['company']); ?></h3>
                                    <p><?php echo nl2br(htmlspecialchars($resume['summary'])); ?></p>
                                    <p><strong>Duration:</strong> <?php echo htmlspecialchars($resume['start_year']); ?> - <?php echo htmlspecialchars($resume['end_year']); ?></p>
                                     <p><strong>Duties:</strong>
                                    <ul>
                                        <?php foreach ($duties[$resume['id']] ?? [] as $duty): ?>
                                            <li><?php echo htmlspecialchars($duty['duty']); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    </p>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                <?php else: ?>
                    <div class="container">
                        <p>No resume entries found.</p> 
                    </div>
                <?php endif; ?>
            </section>
        </main>

 