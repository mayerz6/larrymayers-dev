<?php
$dutiesText = implode("\n", array_map(static fn ($duty) => $duty['duty'], $duties ?? []));
?>

<main>
    <section class="section" id="resume-edit">
        <div class="container">
            <p><a href="/resume/manage">&larr; Back to resume manager</a></p>
            <h1>Edit Resume Entry</h1>
            <p class="muted">Update the selected entry and save the changes to the public resume page.</p>

            <form class="form surface card-body" method="POST" action="/resume/update">
                <input type="hidden" name="id" value="<?php echo (int) $resume['id']; ?>">

                <div class="form-row">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($resume['title']); ?>" required>
                </div>

                <div class="form-row">
                    <label for="company">Company or Organization</label>
                    <input type="text" id="company" name="company" value="<?php echo htmlspecialchars($resume['company']); ?>" required>
                </div>

                <div class="form-row">
                    <label for="summary">Summary</label>
                    <textarea id="summary" name="summary" rows="5"><?php echo htmlspecialchars($resume['summary']); ?></textarea>
                </div>

                <div class="grid grid-2">
                    <div class="form-row">
                        <label for="start_year">Start Year</label>
                        <input type="text" id="start_year" name="start_year" value="<?php echo htmlspecialchars($resume['start_year']); ?>">
                    </div>

                    <div class="form-row">
                        <label for="end_year">End Year</label>
                        <input type="text" id="end_year" name="end_year" value="<?php echo htmlspecialchars($resume['end_year']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <label for="duties">Duties or Highlights</label>
                    <textarea id="duties" name="duties" rows="7"><?php echo htmlspecialchars($dutiesText); ?></textarea>
                </div>

                <div class="hero-actions">
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                    <a class="btn btn-secondary" href="/resume/manage">Cancel</a>
                </div>
            </form>
        </div>
    </section>
</main>
