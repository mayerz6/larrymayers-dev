<?php ?>

        <main>
            <section id="resumeList">
                <?php if (!empty($resumes) && !empty($duties)): ?>
                    <div class="container">
                        <h1>Resume/Projects Catalog</h1>
                        <ol class="resume-list">
                            <?php foreach ($resumes as $resume): ?>
                                <div class="accordion">
                                    <details>
                                        <summary>
                                        <h5>
                                            <?php echo htmlspecialchars($resume['title']); ?> at <?php echo htmlspecialchars($resume['company']); ?>    
                                        <span><?php echo htmlspecialchars($resume['start_year']); ?> - <?php echo htmlspecialchars($resume['end_year']); ?></span>
                                    </h5></summary>
                                        <div class="detail-content">
                                            <p><?php echo nl2br(htmlspecialchars($resume['summary'])); ?></p>
                                            <ul>
                                                <?php foreach ($duties[$resume['id']] ?? [] as $duty): ?>
                                                        <li><?php echo htmlspecialchars($duty['duty']); ?></li>
                                                    <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </details>
                                </div>
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

 