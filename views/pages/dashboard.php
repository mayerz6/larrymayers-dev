<?php ?>

<h2>Mayers Backend Dashboard | Secure Access Only</h2>
<?php if(!empty($flash)) : ?>
<p>
<?= $flash; ?> <br>
Your secure account dashboard is ready. Here you can manage your profile, view messages, and access other backend features.
</p>
<?php else: ?>
Your secure account dashboard is ready to help manage your profile, view messages, and access other backend features.
<?php endif; ?>
<ul>
    <li><a href="/profile/manage">Edit Profile</a></li>
    <li><a href="/messages">View Messages</a></li>
    <!-- <li><a href="/projects/manage">Manage Projects</a></li> -->
    <!-- <li><a href="/expertise/manage">Update Expertise</a></li> -->
    <li><a href="/resume/manage">Edit Resume</a></li>
    <!-- Add more dashboard links as needed -->
</ul>