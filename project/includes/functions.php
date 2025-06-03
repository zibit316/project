<?php
// includes/functions.php
// Допоміжні функції для веб-сайту

// Функція для форматування дати
function formatDate($date) {
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return __('just_now');
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' ' . ($mins == 1 ? __('minute_ago') : __('minutes_ago'));
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' ' . ($hours == 1 ? __('hour_ago') : __('hours_ago'));
    } elseif ($diff < 172800) {
        return __('yesterday');
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' ' . __('days_ago');
    } else {
        return date('d.m.Y', $timestamp);
    }
}

// Функція для коректного виведення повідомлень
function showMessage($message, $type = 'success') {
    if (empty($message)) return '';
    
    $icon = $type == 'success' ? 'check-circle' : 'exclamation-circle';
    $class = $type == 'success' ? 'success' : 'danger';
    
    return '<div class="alert alert-' . $class . ' alert-dismissible fade show" role="alert">
              <i class="fas fa-' . $icon . ' me-2"></i>' . $message . '
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

// Функція для безпечного виведення текстів
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Функція для отримання поточного URL
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// Функція для генерації посилання для спільного доступу
function generateShareLink($listId) {
    $baseUrl = (BASE_URL ?: '/');
    return $baseUrl . 'view_list.php?id=' . $listId;
}

// Функція для перевірки CSRF-токену
function validateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
        return false;
    }
    return true;
}

// Функція для генерації CSRF-токену
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Функція для отримання CSS-класу статусу елементу
function getItemStatusClass($isCompleted) {
    return $isCompleted ? 'completed' : '';
}

// Функція для надсилання JSON-відповіді
function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Функція для перевірки чи використовується AJAX
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Функція для отримання запитуваного URL
function getRequestedUrl() {
    return $_SERVER['REQUEST_URI'];
}

// Функція для перенаправлення з повідомленням
function redirectWithMessage($url, $message = '', $type = 'success') {
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header('Location: ' . $url);
    exit;
}

// Функція для відображення повідомлення з сесії
function showFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        return showMessage($message, $type);
    }
    return '';
}

// Функція для обмеження довжини тексту
function truncateText($text, $length = 50) {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    
    return mb_substr($text, 0, $length) . '...';
}

// Функція для перевірки наявності активного елементу в меню
function isActiveMenu($current, $page) {
    return $current == $page ? 'active' : '';
}