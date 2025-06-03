<?php
// views/lists/create.php - Сторінка створення нового списку
session_start();
require_once '../../config/init.php';
require_once '../../includes/functions.php';

// Перевірка, чи користувач авторизований
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Підключення заголовка
include '../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h1 class="h4 mb-0"><?php echo $lang['create_list']; ?></h1>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                                echo $_SESSION['error']; 
                                unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="../../controllers/lists.php" method="post">
                        <input type="hidden" name="action" value="create_list">
                        
                        <div class="mb-3">
                            <label for="list_name" class="form-label"><?php echo $lang['list_name']; ?></label>
                            <input type="text" class="form-control" id="list_name" name="list_name" 
                                   value="<?php echo isset($_SESSION['form_data']['list_name']) ? htmlspecialchars($_SESSION['form_data']['list_name']) : ''; ?>"
                                   required>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="../../dashboard.php" class="btn btn-secondary">
                                <?php echo $lang['cancel']; ?>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $lang['create']; ?>
                            </button>
                        </div>
                    </form>
                    
                    <?php
                    // Очищення даних форми після відображення
                    if (isset($_SESSION['form_data'])) {
                        unset($_SESSION['form_data']);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Підключення підвала
include '../../includes/footer.php';
?>