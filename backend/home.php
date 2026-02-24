<?php

require_once __DIR__ . '/config/config.php';

// Get search and sort parameters from the query string
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';
// Trim the search term to remove extra whitespace
$search = trim($search);

// Build the SQL query with optional search and sorting
$sql = "SELECT * FROM Article";
$params = [];

// Add a WHERE clause if a search term is provided
if (!empty($search)) {
    $sql .= " WHERE nom LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

// Determine the ORDER BY clause based on the sort parameter
switch ($sort) {
    case 'prix_asc':
        $orderBy = "prix ASC";
        break;
    case 'prix_desc':
        $orderBy = "prix DESC";
        break;
    default:
        $orderBy = "date_publication DESC";
        break;
}
// Append the ORDER BY clause to the SQL query
$sql .= " ORDER BY " . $orderBy;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../frontend/pages/home.php';
