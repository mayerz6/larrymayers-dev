<?php ?>
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