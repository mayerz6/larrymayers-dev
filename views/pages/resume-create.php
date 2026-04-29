<?php ?>

<main>
    <section class="section" id="resume-create">
        <div class="container">
            <p><a href="/resume/manage">&larr; Back to resume manager</a></p>
            <h1>Add Resume Entry</h1>
            <p class="muted">Create a role, project, or professional milestone for the public resume page.</p>

            <?php if (!empty($_SESSION['flash'])): ?>
                <p class="surface card-body"><?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></p>
            <?php endif; ?>

            <form id="resumeForm" class="form surface card-body" method="POST" action="/resume/create">
                <div class="form-row">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div class="form-row">
                    <label for="company">Company or Organization</label>
                    <input type="text" id="company" name="company" required>
                </div>

                <div class="form-row">
                    <label for="summary">Summary</label>
                    <textarea id="summary" name="summary" rows="5"></textarea>
                </div>

                <div class="grid grid-2">
                    <div class="form-row">
                        <label for="start_year">Start Year</label>
                        <input type="text" id="start_year" name="start_year" placeholder="2024">
                    </div>

                    <div class="form-row">
                        <label for="end_year">End Year</label>
                        <input type="text" id="end_year" name="end_year" placeholder="Present">
                    </div>
                </div>

                <div class="form-row">
                    <label for="duties">Duties or Highlights</label>
                    <textarea id="duties" name="duties" rows="7" placeholder="Enter one item per line"></textarea>
                </div>

                <button class="btn btn-primary" type="submit">Add Entry</button>
                <div id="resumeResMsg" class="text-small mt-2"></div>
            </form>
        </div>
    </section>
</main>
