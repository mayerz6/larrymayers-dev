<?php

function projectDb(): PDO
{
    $db = Database::getLiteConnection();
    Database::createProjectsTable($db);
    Database::createProjectDutiesTable($db);
    return $db;
}

function isAjaxRequest(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}