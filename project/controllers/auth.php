<?php
// auth.php - Контролер авторизації
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Обробка реєстрації
if (isset($_POST['action']) && $_POST['action'] == 'register') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Валідація даних
    $errors = [];
    
    if (empty($name)) {
        $errors[] = $lang['name_required'];
    }
    
    if (empty($email)) {
        $errors[] = $lang['email_required'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $lang['invalid_email'];
    }
    
    if (empty($password)) {
        $errors[] = $lang['password_required'];
    } elseif (strlen($password) < 6) {
        $errors[] = $lang['password_min_length'];
    }
    
    if ($password !== $confirm_password) {
        $errors[] = $lang['passwords_not_match'];
    }
    
    // Перевірка, чи існує користувач з таким email
    if (!empty($email) && emailExists($email)) {
        $errors[] = $lang['email_already_exists'];
    }
    
    // Якщо є помилки, повертаємо користувача на сторінку реєстрації
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = [
            'name' => $name,
            'email' => $email
        ];
        header('Location: ../views/auth/register.php');
        exit;
    }
    
    // Реєстрація користувача
    $result = registerUser($name, $email, $password);
    
    if ($result) {
        $_SESSION['success'] = $lang['registration_successful'];
        header('Location: ../views/auth/login.php');
        exit;
    } else {
        $_SESSION['error'] = $lang['registration_failed'];
        header('Location: ../views/auth/register.php');
        exit;
    }
}

// Обробка входу
if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Валідація даних
    $errors = [];
    
    if (empty($email)) {
        $errors[] = $lang['email_required'];
    }
    
    if (empty($password)) {
        $errors[] = $lang['password_required'];
    }
    
    // Якщо є помилки, повертаємо користувача на сторінку входу
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = [
            'email' => $email
        ];
        header('Location: ../views/auth/login.php');
        exit;
    }
    
    // Перевірка користувача
    $user = loginUser($email, $password);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        
        // Якщо користувач вибрав "Запам'ятати мене"
        if ($remember) {
            $token = generateRememberToken();
            $expiry = time() + (30 * 24 * 60 * 60); // 30 днів
            
            // Оновлення remember_token у БД
            updateUserRememberToken($user['id'], $token, $expiry);
            
            // Встановлення cookie
            setcookie('remember_token', $token, $expiry, '/', '', false, true);
            setcookie('user_id', $user['id'], $expiry, '/', '', false, true);
        }
        
        $_SESSION['success'] = $lang['login_successful'];
        header('Location: ../dashboard.php');
        exit;
    } else {
        $_SESSION['error'] = $lang['login_failed'];
        header('Location: ../views/auth/login.php');
        exit;
    }
}

// Обробка виходу
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Видалення remember_token з БД, якщо він існує
    if (isset($_SESSION['user_id'])) {
        updateUserRememberToken($_SESSION['user_id'], null, null);
    }
    
    // Видалення cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    if (isset($_COOKIE['user_id'])) {
        setcookie('user_id', '', time() - 3600, '/', '', false, true);
    }
    
    // Знищення сесії
    session_unset();
    session_destroy();
    
    // Перенаправлення на сторінку входу
    header('Location: ../views/auth/login.php');
    exit;
}

// Якщо дія не визначена, повертаємося на головну сторінку
header('Location: ../index.php');
exit;
?>