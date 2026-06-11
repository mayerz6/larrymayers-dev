<?php
$stats = $stats ?? ['posts' => 0, 'messages' => 0, 'projects' => 0, 'resume' => 0];
$adminEmail = $_SESSION['email'] ?? 'Admin';
?>

<main>
    <section class="section dashboard-shell">
        <div class="container">
            <div class="dashboard-hero">
                <div>
                    <p class="eyebrow">Admin Console</p>
                    <h1>Portfolio Control Center</h1>
                    <p class="lead">Manage content, monitor inbound messages, and keep the public portfolio current.</p>
                </div>

                <div class="dashboard-session card">
                    <span class="dashboard-kicker">Signed in as</span>
                    <strong><?php echo htmlspecialchars($adminEmail); ?></strong>
                    <span class="dashboard-status">Secure session active</span>
                </div>
            </div>

            <?php if (!empty($flash)): ?>
            <div class="dashboard-alert">
                <?php echo htmlspecialchars($flash); ?>
            </div>
            <?php endif; ?>

            <div class="dashboard-stats">
                <article class="stat-card">
                    <span class="stat-label">Blog Posts</span>
                    <strong><?php echo (int) $stats['posts']; ?></strong>
                    <span class="stat-note">Published blog post entries</span>
                </article>

                <article class="stat-card">
                    <span class="stat-label">Messages</span>
                    <strong><?php echo (int) $stats['messages']; ?></strong>
                    <span class="stat-note">Contact inbox records</span>
                </article>

                <article class="stat-card">
                    <span class="stat-label">Projects</span>
                    <strong><?php echo (int) $stats['projects']; ?></strong>
                    <span class="stat-note">Published portfolio items</span>
                </article>

                <article class="stat-card">
                    <span class="stat-label">Resume</span>
                    <strong><?php echo (int) $stats['resume']; ?></strong>
                    <span class="stat-note">Career timeline entries</span>
                </article>
            </div>

            <div class="dashboard-grid">
                <section class="dashboard-panel">
                    <div class="panel-heading">
                        <div>
                            <h2>Content Management</h2>
                            <p class="muted">Update the records that power the public portfolio.</p>
                        </div>
                    </div>

                    <div class="action-list">
                        <a class="action-card" href="/blog/manage">
                            <span class="action-icon">B</span>
                            <span>
                                <strong>Manage Blog Posts</strong>
                                <small>Create, edit, and review blog entries.</small>
                            </span>
                        </a>

                        <a class="action-card" href="/projects/manage">
                            <span class="action-icon">P</span>
                            <span>
                                <strong>Manage Projects</strong>
                                <small>Create, edit, and review portfolio project entries.</small>
                            </span>
                        </a>

                        <a class="action-card" href="/resume/manage">
                            <span class="action-icon">R</span>
                            <span>
                                <strong>Edit Resume</strong>
                                <small>Maintain role history, summaries, and duties.</small>
                            </span>
                        </a>

                        <a class="action-card" href="/messages">
                            <span class="action-icon">M</span>
                            <span>
                                <strong>View Messages</strong>
                                <small>Review inbound contact form submissions.</small>
                            </span>
                        </a>
                    </div>
                </section>

                <aside class="dashboard-panel dashboard-ops">
                    <h2>Operations</h2>
                    <div class="ops-list">
                        <div>
                            <span class="ops-dot"></span>
                            <strong>Database</strong>
                            <small>SQLite persistence enabled</small>
                        </div>
                        <div>
                            <span class="ops-dot"></span>
                            <strong>Routing</strong>
                            <small>Front controller active</small>
                        </div>
                        <div>
                            <span class="ops-dot"></span>
                            <strong>Admin Access</strong>
                            <small>Session-protected dashboard</small>
                        </div>
                    </div>

                    <div class="dashboard-actions">
                        <a class="btn btn-secondary" href="/projects">Public Projects</a>
                        <a class="btn btn-secondary" href="/resume">Public Resume</a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</main>