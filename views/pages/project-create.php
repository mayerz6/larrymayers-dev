<?php ?>

<main>
    <section class="section" id="project-create">
        <div class="container">
            <p><a href="/projects/manage">&larr; Back to project manager</a></p>
            <h1>Add project Entry</h1>
            <p class="muted">Create a role, project, or professional milestone for the public project page.</p>

            <?php if (!empty($_SESSION['flash'])): ?>
                <p class="surface card-body"><?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></p>
            <?php endif; ?>

            <form id="projectForm" class="form card-body" method="POST" action="/projects/create">
                <fieldset>
                    <legend>Project Details</legend>
                    <!-- <p>Provide details about your project, including the title, description, technologies used, and any relevant links.</p> -->
                <div class="form-row">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div class="form-row">
                    <label for="description">Description of Project</label>
                    <input type="text" id="description" name="description" required>
                </div>

                <div class="form-row">
                    <label for="link">Link</label>
                    <input type="text" id="link" name="link" required>
                </div>

                    <div class="form-row">
                        <label for="technologies">Technologies Used</label>
                        <textarea id="technologies" name="technologies" rows=7 placeholder="Enter one item per line" required></textarea>
                    </div>
                    <br>
                     <div class="form-row">
                    <button class="btn btn-primary" type="submit">Add Project</button>
                    <div id="projectResMsg" class="text-small mt-2"></div>
                    </div>
            </fieldset>
            </form>
        </div>
    </section>
</main>
