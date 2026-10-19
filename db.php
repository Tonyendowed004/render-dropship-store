<?php
declare(strict_types=1);
$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: '';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';
if (!$host || !$dbname || !$user) { http_response_code(500); exit('Database configuration is missing. Set DB_HOST, DB_NAME and DB_USER in Render.'); }
$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false];
try { $pdo = new PDO($dsn, $user, $pass, $options); }
catch (PDOException $e) { error_log('Database connection failed: '.$e->getMessage()); http_response_code(500); exit('Database connection failed. Check Render environment variables and Aiven access settings.'); }
