<?php
require_once 'init.php';

// Якщо користувач вже увійшов, перенаправляємо на головну сторінку
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

// Обробка форми реєстрації
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Усі поля є обов\'язковими для заповнення';
    } elseif ($password !== $confirm_password) {
        $error = 'Паролі не співпадають';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль повинен містити не менше 6 символів';
    } else {
        $result = registerUser($username, $email, $password);
        
        if ($result['status'] === 'success') {
            header("Location: index.php");
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$page_title = $lang['register_title'];
include 'header.php';
?>

<main class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2><?= $lang['register_title'] ?></h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="register.php">
                        <div class="form-group">
                            <label for="username"><?= $lang['username'] ?></label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="email"><?= $lang['email'] ?></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password"><?= $lang['password'] ?></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password"><?= $lang['confirm_password'] ?></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-success btn-block"><?= $lang['register'] ?></button>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0"><?= $lang['already_have_account'] ?> <a href="login.php"><?= $lang['login'] ?></a></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>