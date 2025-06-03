<?php
// config/config.php
// Конфігурація бази даних та налаштування програми

// Налаштування бази даних
define('DB_HOST', 'localhost'); // Хост бази даних
define('DB_USER', 'root');      // Користувач бази даних
define('DB_PASS', '');          // Пароль бази даних
define('DB_NAME', 'todo_app');  // Назва бази даних

// Налаштування сайту
define('SITE_NAME', 'Мої Списки'); // Назва сайту
define('DEFAULT_LANG', 'ua');      // Мова за замовчуванням

// Налаштування шляхів
define('BASE_URL', ''); // Базовий URL сайту (залиште порожнім для автоматичного визначення)
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('VIEWS_PATH', ROOT_PATH . 'views/');
define('LANG_PATH', ROOT_PATH . 'lang/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');

// Налаштування сесій
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));