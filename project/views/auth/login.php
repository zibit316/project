<?php
require_once 'init.php';

// Якщо користувач вже увійшов, перенаправляємо на головну сторінку
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

// Обробка форми входу
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Введіть електронну пошту та пароль';
    } else {
        $result = loginUser($email, $password);
        
        if ($result['status'] === 'success') {
            header("Location: index.php");
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$page_title = $lang['login_title'];
include 'header.php';
?>

<main class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2><?= $lang['login_title'] ?></h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="login.php">
                        <div class="form-group">
                            <label for="email"><?= $lang['email'] ?></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password"><?= $lang['password'] ?></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember"><?= $lang['remember_me'] ?></label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block"><?= $lang['login'] ?></button>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0"><?= $lang['dont_have_account'] ?> <a href="register.php"><?= $lang['register'] ?></a></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>