<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$input = json_decode(file_get_contents('php://input'), true);

if (
    !isset($input['first_name'], $input['last_name'], $input['email'], $input['password'], $input['role'])
    || empty($input['first_name']) || empty($input['last_name'])
    || empty($input['email']) || empty($input['password']) || empty($input['role'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

$first_name = trim($input['first_name']);
$last_name  = trim($input['last_name']);
$email      = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
$password   = password_hash($input['password'], PASSWORD_DEFAULT);
$role       = $input['role'];

if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$first_name, $last_name, $email, $password, $role]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Email already exists or server error: ' . $e->getMessage()]);
}
