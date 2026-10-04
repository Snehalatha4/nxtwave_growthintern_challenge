<?php
/**
 * Header Component with Responsive Navigation & Demo Indicator
 */
require_once __DIR__ . '/../config/database.php';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> — <?php echo APP_TAGLINE; ?></title>
    <meta name="description" content="A free hands-on workshop for final-year engineering students to build their first AI project in 60 minutes. Built for NxtWave Growth Challenge.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
</head>
<body>

<!-- Simulation / Demo Banner -->
<aside class="demo-banner" aria-label="Prototype Notice">
    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <span class="demo-banner-badge">⚡ PROTOTYPE / SIMULATION</span>
        <span>Independent Submission for <strong>NxtWave Growth Challenge</strong> (Goal: 500 Registrations | ₹2,000 Budget)</span>
    </div>
    <div style="display: flex; gap: 14px; align-items: center;">
        <a href="admin.php">Growth Ops ↗</a>
        <a href="experiments.php">A/B Tests ↗</a>
    </div>
</aside>

<!-- Navigation Bar -->
<header>
<nav class="navbar" aria-label="Main Navigation">
    <div class="container nav-inner">
        <a href="index.php" class="brand-logo" aria-label="BUILD IN 60 Home">
            <div class="brand-icon">⚡</div>
            <span><?php echo APP_NAME; ?></span>
        </a>

        <ul class="nav-links">
            <li><a href="index.php" class="<?php echo $currentPage === 'index' ? 'active' : ''; ?>">Workshop</a></li>
            <li><a href="index.php#growth-loop">Growth Loop</a></li>
            <li><a href="dashboard.php" class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">Student Dashboard</a></li>
            <li><a href="experiments.php" class="<?php echo $currentPage === 'experiments' ? 'active' : ''; ?>">A/B Experiments</a></li>
            <li><a href="admin.php" class="<?php echo $currentPage === 'admin' ? 'active' : ''; ?>">Growth Ops Admin</a></li>
        </ul>

        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="register.php" class="btn btn-primary btn-sm js-register-btn">Reserve Free Seat</a>
        </div>
    </div>
</nav>
</header>
