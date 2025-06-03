<?php
// models/User.php
// Модель для роботи з користувачами

class User {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Реєстрація нового користувача
    public function register($username, $email, $password) {
        // Перевірка унікальності імені користувача
        if ($this->usernameExists($username)) {
            return ['success' => false, 'message' => __('username_exists')];
        }
        
        // Перевірка унікальності email
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => __('email_exists')];
        }
        
        // Хешування пароля
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Підготовка запиту
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, lang) VALUES (?, ?, ?, ?)");
        $lang = DEFAULT_LANG;
        $stmt->bind_param("ssss", $username, $email, $hashedPassword, $lang);
        
        // Виконання запиту
        if ($stmt->execute()) {
            return ['success' => true, 'message' => __('account_created')];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }
    
    // Вхід користувача
    public function login($usernameOrEmail, $password) {
        // Пошук користувача за іменем або email
        $stmt = $this->db->prepare("SELECT id, username, email, password, lang FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Перевірка пароля
            if (password_verify($password, $user['password'])) {
                // Збереження даних в сесію
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_lang'] = $user['lang'];
                
                // Встановлення cookie для мови
                setcookie('lang', $user['lang'], time() + (86400 * 30), "/"); // 30 днів
                
                return ['success' => true, 'message' => __('login_success')];
            }
        }
        
        return ['success' => false, 'message' => __('login_error')];
    }
    
    // Вихід користувача
    public function logout() {
        // Видалення сесійних змінних
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['user_lang']);
        
        // Знищення сесії
        session_destroy();
        
        return ['success' => true, 'message' => __('logout_success')];
    }
    
    // Отримання інформації про користувача за ID
    public function getUserById($userId) {
        $stmt = $this->db->prepare("SELECT id, username, email, created_at, lang FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Оновлення профілю користувача
    public function updateProfile($userId, $username, $email, $lang) {
        // Перевірка унікальності імені користувача
        if ($this->usernameExists($username, $userId)) {
            return ['success' => false, 'message' => __('username_exists')];
        }
        
        // Перевірка унікальності email
        if ($this->emailExists($email, $userId)) {
            return ['success' => false, 'message' => __('email_exists')];
        }
        
        // Підготовка запиту
        $stmt = $this->db->prepare("UPDATE users SET username = ?, email = ?, lang = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $lang, $userId);
        
        // Виконання запиту
        if ($stmt->execute()) {
            // Оновлення сесійних даних
            $_SESSION['username'] = $username;
            $_SESSION['user_lang'] = $lang;
            
            // Оновлення cookie для мови
            setcookie('lang', $lang, time() + (86400 * 30), "/"); // 30 днів
            
            return ['success' => true, 'message' => __('profile_updated')];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }
    
    // Зміна пароля користувача
    public function changePassword($userId, $currentPassword, $newPassword) {
        // Отримання поточного пароля
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Перевірка поточного пароля
            if (password_verify($currentPassword, $user['password'])) {
                // Хешування нового пароля
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Оновлення пароля
                $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashedPassword, $userId);
                
                if ($stmt->execute()) {
                    return ['success' => true, 'message' => __('password_changed')];
                } else {
                    return ['success' => false, 'message' => $stmt->error];
                }
            } else {
                return ['success' => false, 'message' => __('invalid_current_password')];
            }
        }
        
        return ['success' => false, 'message' => __('user_not_found')];
    }
    
    // Перевірка існування імені користувача
    private function usernameExists($username, $excludeUserId = null) {
        if ($excludeUserId) {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->bind_param("si", $username, $excludeUserId);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    // Перевірка існування email
    private function emailExists($email, $excludeUserId = null) {
        if ($excludeUserId) {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $excludeUserId);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    // Пошук користувача за email
    public function findUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT id, username, email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
}