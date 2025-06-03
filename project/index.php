<?php
// Ініціалізація проекту
require_once 'init.php';

// Отримання списків користувача
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$lists = [];

if ($user_id) {
    $lists = getUserLists($user_id);
}

$page_title = 'Головна сторінка';
include 'header.php';
?>

<main class="container mt-4">
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="row mb-4">
            <div class="col-12">
                <h2><?= $lang['my_lists'] ?></h2>
                <button class="btn btn-primary" data-toggle="modal" data-target="#createListModal">
                    <i class="fa fa-plus"></i> <?= $lang['create_new_list'] ?>
                </button>
            </div>
        </div>
        
        <div class="row">
            <?php if (empty($lists)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <?= $lang['no_lists_yet'] ?>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($lists as $list): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($list['name']) ?></h5>
                            <p class="card-text">
                                <?= $lang['items_count'] ?>: <?= $list['item_count'] ?><br>
                                <?= $lang['created_at'] ?>: <?= date('d.m.Y', strtotime($list['created_at'])) ?>
                            </p>
                            <a href="list.php?id=<?= $list['id'] ?>" class="btn btn-info">
                                <i class="fa fa-eye"></i> <?= $lang['view'] ?>
                            </a>
                            <button class="btn btn-danger delete-list" data-id="<?= $list['id'] ?>">
                                <i class="fa fa-trash"></i> <?= $lang['delete'] ?>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2><?= $lang['welcome_title'] ?></h2>
                        <p><?= $lang['welcome_text'] ?></p>
                        <div class="text-center mb-3">
                            <a href="login.php" class="btn btn-primary mr-2"><?= $lang['login'] ?></a>
                            <a href="register.php" class="btn btn-success"><?= $lang['register'] ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3><?= $lang['features'] ?></h3>
                        <ul>
                            <li><?= $lang['feature_1'] ?></li>
                            <li><?= $lang['feature_2'] ?></li>
                            <li><?= $lang['feature_3'] ?></li>
                            <li><?= $lang['feature_4'] ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<!-- Модальне вікно для створення списку -->
<div class="modal fade" id="createListModal" tabindex="-1" role="dialog" aria-labelledby="createListModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createListModalLabel"><?= $lang['create_new_list'] ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="createListForm">
                    <div class="form-group">
                        <label for="listName"><?= $lang['list_name'] ?></label>
                        <input type="text" class="form-control" id="listName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="listDescription"><?= $lang['list_description'] ?></label>
                        <textarea class="form-control" id="listDescription" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $lang['cancel'] ?></button>
                <button type="button" class="btn btn-primary" id="saveNewList"><?= $lang['create'] ?></button>
            </div>
        </div>
    </div>
</div>

<script src="js/lists.js"></script>

<?php include 'footer.php'; ?>