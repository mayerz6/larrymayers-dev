<?php

function requireAuth(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit();
    }
}
