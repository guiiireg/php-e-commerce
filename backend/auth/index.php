<?php
/**
 * Authentication Directory Guard
 *
 * Serves as a gateway/fallback to prevent directory index exposure,
 * routing unauthenticated visitors to login and authenticated members to the homepage.
 */

require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: /backend/auth/login.php");
    exit;
}

header("Location: /backend/home.php");
exit;