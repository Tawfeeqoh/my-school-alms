<?php
// ============================================================
// ALMS — Common Dashboard Header Include
// ============================================================
require_once __DIR__ . '/../config.php';

// Default metadata
$pageTitle = $pageTitle ?? 'Academic Command Center';
$pageDesc = $pageDesc ?? 'FCAH&PT Ibadan Advanced Learning Management System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — ALMS</title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
    
    <!-- Design System Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- System CSS Sheets -->
    <link rel="stylesheet" href="/assets/css/index.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    
    <meta name="csrf-token" content="<?= csrfToken() ?>">
    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body class="film-grain">
<div class="dashboard-shell">
