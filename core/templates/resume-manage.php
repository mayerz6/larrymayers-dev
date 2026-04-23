<?php ?>


        <main>
            <section id="resume-manage">
                <div class="container">
                    <h2>Manage Resume</h2>
                    <p>Here you can manage your resume entries. Add new experiences, edit existing ones, or remove outdated information.</p>
                    <!-- Resume management form and list will go here -->

                    <form id="resumeForm" method="POST">
                        <fieldset>
                            <legend>Add New Resume Entry</legend>           
                            <label for="title">Job Title:</label>
                            <input type="text" id="title" name="title" required>
                            <br>
                            <br>
                            <label for="company">Company:</label>
                            <input type="text" id="company" name="company" required>
                            <br>
                            <br>
                            <label for="summary">Summary:</label>
                            <textarea id="summary" name="summary" rows="5"></textarea>
                            <br>
                            <br>
                            <label for="start_year">Start Year:</label>
                            <input type="text" id="start_year" name="start_year">
                            <br>
                            <br>
                            <label for="end_year">End Year:</label>
                            <input type="text" id="end_year" name="end_year">
                            <br>
                            <br>
                            <label for="duties">Position Duties:</label>
                            <textarea id="duties" name="duties" rows="5" placeholder="Enter duties (one per line)"></textarea>
                            <br>
                            <br>
                            <button type="submit">Add Entry</button>
                        </fieldset>
                        <div id="resumeResMsg" class="text-small mt-2"></div> 
                    </form>
                </div>
            </section>

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

