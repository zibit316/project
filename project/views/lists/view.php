<?php
// views/lists/view.php - Сторінка перегляду списку
session_start();
require_once '../../config/init.php';
require_once '../../includes/functions.php';

// Перевірка, чи користувач авторизований
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Отримання ідентифікатора списку
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = $lang['invalid_list_id'];
    header('Location: ../../dashboard.php');
    exit;
}

$list_id = (int)$_GET['id'];

// Отримання інформації про список
$list = getListById($list_id);

// Перевірка, чи належить список поточному користувачеві
if (!$list || $list['user_id'] != $user_id) {
    $_SESSION['error'] = $lang['access_denied'];
    header('Location: ../../dashboard.php');
    exit;
}

// Отримання елементів списку
$items = getListItems($list_id);

// Підключення заголовка
include '../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-3">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../dashboard.php"><?php echo $lang['dashboard']; ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($list['list_name']); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <h1><?php echo htmlspecialchars($list['list_name']); ?></h1>
                <div>
                    <a href="edit.php?id=<?php echo $list_id; ?>" class="btn btn-secondary">
                        <i class="bi bi-pencil"></i> <?php echo $lang['edit_list']; ?>
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal">
                        <i class="bi bi-trash"></i> <?php echo $lang['delete_list']; ?>
                    </button>
                </div>
            </div>
            <p class="text-muted">
                <?php echo $lang['created_at']; ?>: <?php echo formatDate($list['created_at']); ?>
                <?php if (isset($list['updated_at']) && $list['updated_at']): ?>
                    | <?php echo $lang['updated_at']; ?>: <?php echo formatDate($list['updated_at']); ?>
                <?php endif; ?>
            </p>
        </div>
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

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0"><?php echo $lang['add_item']; ?></h2>
                </div>
                <div class="card-body">
                    <form action="../../controllers/lists.php" method="post">
                        <input type="hidden" name="action" value="add_item">
                        <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
                        
                        <div class="mb-3">
                            <label for="item_name" class="form-label"><?php echo $lang['item_name']; ?></label>
                            <input type="text" class="form-control" id="item_name" name="item_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quantity" class="form-label"><?php echo $lang['quantity']; ?></label>
                            <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1">
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label"><?php echo $lang['notes']; ?></label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> <?php echo $lang['add_item']; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0"><?php echo $lang['items']; ?></h2>
                </div>
                <div class="card-body">
                    <?php if (empty($items)): ?>
                        <div class="alert alert-info">
                            <?php echo $lang['no_items']; ?>
                        </div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush" id="items-list">
                            <?php foreach ($items as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input item-checkbox" type="checkbox" 
                                               data-item-id="<?php echo $item['id']; ?>"
                                               <?php echo $item['is_completed'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label <?php echo $item['is_completed'] ? 'text-decoration-line-through' : ''; ?>">
                                            <?php echo htmlspecialchars($item['item_name']); ?>
                                            <?php if ($item['quantity'] > 1): ?>
                                                <span class="badge bg-secondary rounded-pill"><?php echo $item['quantity']; ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['notes'])): ?>
                                                <small class="text-muted d-block"><?php echo htmlspecialchars($item['notes']); ?></small>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                    <div class="d-flex">
                                        <button type="button" class="btn btn-sm btn-outline-secondary me-2 edit-item-btn"
                                                data-item-id="<?php echo $item['id']; ?>"
                                                data-item-name="<?php echo htmlspecialchars($item['item_name']); ?>"
                                                data-item-quantity="<?php echo $item['quantity']; ?>"
                                                data-item-notes="<?php echo htmlspecialchars($item['notes']); ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="../../controllers/lists.php" method="post" class="d-inline">
                                            <input type="hidden" name="action" value="delete_item">
                                            <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('<?php echo $lang['confirm_delete_item']; ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальне вікно підтвердження видалення списку -->
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
                <form action="../../controllers/lists.php" method="post">
                    <input type="hidden" name="action" value="delete_list">
                    <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
                    <button type="submit" class="btn btn-danger"><?php echo $lang['delete']; ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модальне вікно редагування елемента -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editItemModalLabel"><?php echo $lang['edit_item']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../../controllers/lists.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_item">
                    <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
                    <input type="hidden" name="item_id" id="edit_item_id">
                    
                    <div class="mb-3">
                        <label for="edit_item_name" class="form-label"><?php echo $lang['item_name']; ?></label>
                        <input type="text" class="form-control" id="edit_item_name" name="item_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_quantity" class="form-label"><?php echo $lang['quantity']; ?></label>
                        <input type="number" class="form-control" id="edit_quantity" name="quantity" value="1" min="1">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_notes" class="form-label"><?php echo $lang['notes']; ?></label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $lang['cancel']; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $lang['update']; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обробка натискання на чекбокси елементів
    document.querySelectorAll('.item-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const itemId = this.getAttribute('data-item-id');
            const isCompleted = this.checked ? 1 : 0;
            const label = this.nextElementSibling;
            
            // Застосування перекреслення
            if (isCompleted) {
                label.classList.add('text-decoration-line-through');
            } else {
                label.classList.remove('text-decoration-line-through');
            }
            
            // AJAX-запит для оновлення статусу елемента
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../../controllers/lists.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status !== 200) {
                    console.error('Помилка при оновленні статусу елемента');
                }
            };
            xhr.send('action=toggle_item_status&item_id=' + itemId + '&status=' + isCompleted);
        });
    });
    
    // Обробка натискання на кнопку редагування елемента
    document.querySelectorAll('.edit-item-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id');
            const itemName = this.getAttribute('data-item-name');
            const itemQuantity = this.getAttribute('data-item-quantity');
            const itemNotes = this.getAttribute('data-item-notes');
            
            document.getElementById('edit_item_id').value = itemId;
            document.getElementById('edit_item_name').value = itemName;
            document.getElementById('edit_quantity').value = itemQuantity;
            document.getElementById('edit_notes').value = itemNotes;
            
            const editItemModal = new bootstrap.Modal(document.getElementById('editItemModal'));
            editItemModal.show();
        });
    });
});
</script>

<?php
// Підключення підвала
include '../../includes/footer.php';
?>