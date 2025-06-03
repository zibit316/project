<?php
// config/init.php
// Ініціалізація бази даних, сесій та загальних налаштувань

// Підключення файлу конфігурації
require_once __DIR__ . '/config.php';

// Старт сесії
session_start();

// Підключення до бази даних
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Перевірка з'єднання
    if ($conn->connect_error) {
        die("Помилка підключення до бази даних: " . $conn->connect_error);
    }
    
    // Встановлення кодування
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// Отримання глобального з'єднання з базою даних
$db = connectDB();

// Отримання поточної мови користувача
function getCurrentLang() {
    if (isset($_SESSION['user_lang'])) {
        return $_SESSION['user_lang'];
    } elseif (isset($_COOKIE['lang'])) {
        return $_COOKIE['lang'];
    }
    return DEFAULT_LANG;
}

// Завантаження мовного файлу
$lang = getCurrentLang();
$langFile = LANG_PATH . $lang . '.php';

if (!file_exists($langFile)) {
    $langFile = LANG_PATH . DEFAULT_LANG . '.php';
}

require_once $langFile;

// Функція перекладу
function __($key) {
    global $LANG;
    return isset($LANG[$key]) ? $LANG[$key] : $key;
}

// Завантаження допоміжних функцій
require_once INCLUDES_PATH . 'functions.php';

// Автозавантаження класів моделей
spl_autoload_register(function($className) {
    $file = ROOT_PATH . 'models/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Функція перевірки авторизації користувача
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Функція для перенаправлення неавторизованих користувачів
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . (BASE_URL ?: '/') . 'login.php');
        exit;
    }
}

// Отримання поточного користувача
function getCurrentUser() {
    if (isLoggedIn()) {
        global $db;
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    return null;
}