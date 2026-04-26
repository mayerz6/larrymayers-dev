<?php ?>

        <header class="site-header">
            <div class="container">
                <nav class="nav" aria-label="Primary navigation">
                    <a class="brand" href="/profile" aria-label="Larry Mayers home">
                        <!-- <span class="brand-mark">LM</span> -->
                        <span class="brand-mark"><img src="/assets/img/lm-graphic.png" alt="Larry Mayers" width="36" height="36"></span>
                        <span>Larry Mayers</span>
                    </a>

                    <button
                        class="nav-toggle"
                        type="button"
                        aria-label="Toggle navigation"
                        aria-controls="primary-navigation"
                        aria-expanded="false"
                    >
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>

                    <ul class="nav-links" id="primary-navigation">
                        <li><a href="/profile">Profile</a></li>
                        <li><a href="/about">About</a></li>
                        <!-- <li><a href="/projects">Projects</a></li> -->
                        <li><a href="/contact">Contact</a></li>
                        <li><a href="/resume">Resume</a></li>
                         <?php if (!empty($_SESSION['user_id'])): ?>
                            <li><a href="/dashboard">Dashboard</a></li>
                            <li><a href="#" id="logoutForm">Logout</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </header>

        <div id="app">
