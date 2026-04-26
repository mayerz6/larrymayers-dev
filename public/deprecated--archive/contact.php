<?php

require_once '../app/Controllers/ContactController.php';

$controller = new ContactController();
$controller->submit($_POST);