<?php
/**
 * Frontend Directory Fallback
 *
 * Redirects direct folder access attempts back to the home controller.
 */
header("Location: /backend/home.php");
exit;
