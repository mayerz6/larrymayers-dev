<?php

class PartialController {
    public function render($page) {
        $validPages = ['about', 'contact', 'expertise', 'resume'];

        if(!in_array($page, $validPages)) {
            http_response_code(404);
            echo "<p>Section not found.</p>";
            return;
        }

        include "../app/Views/Partials/{$page}.php";
    }
}