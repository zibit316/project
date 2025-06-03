<?php
// includes/header.php
// Шапка сайту

// Ініціалізація системи
require_once __DIR__ . '/../config/init.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' . __(SITE_NAME) : __(SITE_NAME); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Власні стилі -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- SortableJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?php echo __(SITE_NAME); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActiveMenu(getRequestedUrl(), '/index.php'); ?>" 
                               href="index.php"><?php echo __('dashboard'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActiveMenu(getRequestedUrl(), '/my_lists.php'); ?>" 
                               href="my_lists.php"><?php echo __('my_lists'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActiveMenu(getRequestedUrl(), '/shared_lists.php'); ?>" 
                               href="shared_lists.php"><?php echo __('shared_lists'); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i><?php echo e($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="profile.php"><?php echo __('profile'); ?></a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><?php echo __('logout'); ?></a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActiveMenu(getRequestedUrl(), '/login.php'); ?>" 
                               href="login.php"><?php echo __('login'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActiveMenu(getRequestedUrl(), '/register.php'); ?>" 
                               href="register.php"><?php echo __('register'); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </header>

    <main class="container my-4">
        <?php echo showFlashMessage(); ?>