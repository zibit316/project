<?php
// dashboard.php - Головна сторінка після входу
session_start();
require_once 'config/init.php';
require_once 'includes/functions.php';

// Перевірка, чи користувач авторизований
if (!isset($_SESSION['user_id'])) {
    header('Location: views/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Отримання інформації про користувача
$user = getUserById($user_id);

// Отримання списків користувача
$lists = getUserLists($user_id);

// Підключення заголовка
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><?php echo $lang['dashboard']; ?></h1>
                <a href="views/lists/create.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> <?php echo $lang['create_list']; ?>
                </a>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <!-- Бічна панель -->
            <div class="card mb-4">
                <div class="card-header">
                    <?php echo $lang['profile']; ?>
                </div>
                <div class="card-body">
                    <p>
                        <strong><?php echo $lang['name']; ?>:</strong> 
                        <?php echo htmlspecialchars($user['name']); ?>
                    </p>
                    <p>
                        <strong><?php echo $lang['email']; ?>:</strong> 
                        <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <p>
                        <a href="profile.php" class="btn btn-sm btn-outline-secondary">
                            <?php echo $lang['edit_profile']; ?>
                        </a>
                    </p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <?php echo $lang['statistics']; ?>
                </div>
                <div class="card-body">
                    <p>
                        <strong><?php echo $lang['total_lists']; ?>:</strong> 
                        <?php echo count($lists); ?>
                    </p>
                    <!-- Додаткова статистика може бути додана тут -->
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <?php echo $lang['my_lists']; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($lists)): ?>
                        <div class="alert alert-info">
                            <?php echo $lang['no_lists']; ?>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($lists as $list): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($list['list_name']); ?></h5>
                                            <p class="card-text text-muted small">
                                                <?php echo $lang['list_created']; ?>: 
                                                <?php echo formatDate($list['created_at']); ?>
                                            </p>
                                            <?php 
                                                // Отримання кількості елементів у списку
                                                $itemCount = getListItemCount($list['id']);
                                                $completedCount = getCompletedItemCount($list['id']);
                                            ?>
                                            <p class="card-text">
                                                <?php echo $completedCount; ?>/<?php echo $itemCount; ?> <?php echo $lang['completed']; ?>
                                            </p>
                                            <div class="progress mb-3">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?php echo $itemCount > 0 ? ($completedCount / $itemCount * 100) : 0; ?>%" 
                                                     aria-valuenow="<?php echo $itemCount > 0 ? ($completedCount / $itemCount * 100) : 0; ?>" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <div class="btn-group w-100" role="group">
                                                <a href="views/lists/view.php?id=<?php echo $list['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> <?php echo $lang['view']; ?>
                                                </a>
                                                <a href="views/lists/edit.php?id=<?php echo $list['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i> <?php echo $lang['edit']; ?>
                                                </a>
                                                <a href="#" class="btn btn-sm btn-outline-danger" 
                                                   onclick="confirmDelete(<?php echo $list['id']; ?>)">
                                                    <i class="bi bi-trash"></i> <?php echo $lang['delete']; ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальне вікно підтвердження видалення -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel"><?php echo $lang['confirm_delete']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php echo $lang['confirm_delete_list']; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $lang['cancel']; ?></button>
                <form id="deleteForm" method="POST" action="controllers/lists.php">
                    <input type="hidden" name="action" value="delete_list">
                    <input type="hidden" name="list_id" id="listIdToDelete">
                    <button type="submit" class="btn btn-danger"><?php echo $lang['delete']; ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(listId) {
    document.getElementById('listIdToDelete').value = listId;
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}
</script>

<?php
// Підключення підвала
include 'includes/footer.php';
?>