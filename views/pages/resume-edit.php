<?php ?>


        <main>
            
            <section id="resumeEdit">
                <div class="container">
                    <h1>Edit Resume</h1>
                    <p>Use the form below to update your resume details. Make sure to fill in all required fields before submitting.</p>
                    
                    <!-- Resume Edit Form -->

<form method="POST" action="/resume/update">
    <input type="hidden" name="id" value="<?= $resume['id'] ?>">

    <input name="title" value="<?= htmlspecialchars($resume['title']) ?>">
    <input name="company" value="<?= htmlspecialchars($resume['company']) ?>">

    <textarea name="summary"><?= htmlspecialchars($resume['summary']) ?></textarea>

    <textarea name="duties">
<?= implode("\n", array_column($duties, 'duty')) ?>
    </textarea>

    <button type="submit">Update</button>
</form>

                </div>
            </section>
        </main> 