<?php ?>


<h2>Secure Access Only | Login to Dashboard<</h2>
<p>Please enter your credentials to access the dashboard.</p>

<form id="loginForm" method="POST" action="/login">
    <fieldset>
        <legend>Account Credentials</legend>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit">Login</button>
    </fieldset> 
</form>