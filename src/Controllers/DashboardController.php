<?php

function dashboard(): void {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    
    requireAuth();
    view('dashboard', ['flash' => $flash]);
}
