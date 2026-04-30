<?php

function projectDb(): PDO
{
    $db = Database::getLiteConnection();
    Database::createProjectsTable($db);
    Database::createProjectDutiesTable($db);
    return $db;
}