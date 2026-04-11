<div class="card">
    <div class="card-header">
        <h2>Messages | Backend Inbox</h2>
        <p class="text-muted"><small>View messages sent through the contact form.</small></p>
        </div>  
<?php if (empty($messages)): ?>
    <div class="card-body">
        <p>No messages found.</p>
    </div>
<?php else: ?>
    <div class="messages-list">
        <?php foreach ($messages as $msg): ?>
            <div class="message-item">
                <div class="message-header  d-flex justify-content-between align-items-center">
                    <h3><?php echo htmlspecialchars($msg['name']); ?> <small>&lt;<?php echo htmlspecialchars($msg['email']); ?>&gt;</small></h3>
                    <span class="badge badge-secondary"><?php echo date("F j, Y, g:i a", strtotime($msg['created_at'])); ?></span>
                </div>
                <div class="message-body"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                <div class="message-footer text-muted">
                    <small>Received on <?php echo date("F j, Y, g:i a", strtotime($msg['created_at'])); ?></small>
                    <button class="delete-btn" data-id="<?= $msg['id'] ?>">Delete</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
</div>  