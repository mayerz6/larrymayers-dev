<?php ?>


<h1>Secure Access Only | Login to Dashboard</h1>
<p>Please enter your credentials to access the dashboard.</p>

<form id="loginForm" method="POST" action="/login">
    <fieldset>
        <legend><h2>Account Credentials</h2></legend>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button class="btn btn-primary mb-3" type="submit">Login</button>
    </fieldset> 
    <div id="loginResMsg" class="text-small mt-2"></div>
</form>