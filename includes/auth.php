<?php
/**
 * Authentication and Session Management Helper
 */
require_once __DIR__ . '/../config/database.php';

function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdminAuth(): void {
    if (!isAdminLoggedIn()) {
        header('Location: admin.php?action=login');
        exit;
    }
}

function getCurrentStudent(): ?array {
    if (isset($_SESSION['student_user_id'])) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['student_user_id']]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
    return null;
}
