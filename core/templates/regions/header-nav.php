<?php ?>

        <header>
            <div class="container">
               <nav>
                    <ul>
                        <li><a href="/profile">Profile</a></li>
                        <li><a href="/about">About</a></li>
                        <!-- <li><a href="/projects">Projects</a></li> -->
                        <li><a href="/contact">Contact</a></li>
                        <!-- <li><a href="/expertise">Expertise</a></li> -->
                        <li><a href="/resume">Resume</a></li>
                        <!-- <li><a href="/messages">Messages</a></li> -->
                         <?php if (!empty($_SESSION['user_id'])): ?>
                            <li><a href="/dashboard">Dashboard</a></li>
                            <!-- <li>
                                <form id="logoutForm" method="POST">
                                    <button type="submit">Logout</button>
                                </form>
                            </li> -->
                            <li><a href="#" id="logoutForm">Logout</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </header>

        <div id="app">