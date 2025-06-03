<?php
// Перевірка авторизації користувача
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?view=auth/login');
    exit;
}

// Отримання списків користувача з контролера
$userId = $_SESSION['user_id'];
$lists = [];

// Перевірка, чи є пошуковий запит
$searchTerm = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

if (!empty($searchTerm)) {
    // Якщо є пошуковий запит, отримуємо результати пошуку
    require_once 'controllers/lists.php';
    $listController = new ListController();
    $lists = $listController->searchLists($userId, $searchTerm);
} else {
    // Інакше отримуємо всі списки користувача
    require_once 'controllers/lists.php';
    $listController = new ListController();
    $lists = $listController->getUserLists($userId);
}

// Отримання локалізованих текстів
$lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'ua';
require_once "lang/{$lang}.php";
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div id="alerts"></div>
    
    <div class="dashboard-header">
        <h1><?php echo $lang_texts['my_lists']; ?></h1>
        <div class="dashboard-actions">
            <a href="index.php?view=lists/create" class="btn"><?php echo $lang_texts['create_list']; ?></a>
            
            <form id="searchForm" class="search-form">
                <input type="text" id="searchTerm" name="search" placeholder="<?php echo $lang_texts['search_lists']; ?>" value="<?php echo $searchTerm; ?>" class="form-control">
                <button type="submit" class="btn"><?php echo $lang_texts['search']; ?></button>
            </form>
        </div>
    </div>
    
    <?php if (!empty($searchTerm)): ?>
    <div class="search-results-info">
        <p><?php echo $lang_texts['search_results_for']; ?> "<?php echo $searchTerm; ?>"</p>
        <a href="index.php?view=lists/index" class="btn-link"><?php echo $lang_texts['clear_search']; ?></a>
    </div>
    <?php endif; ?>
    
    <?php if (empty($lists)): ?>
    <div class="empty-state">
        <?php if (!empty($searchTerm)): ?>
            <p><?php echo $lang_texts['no_search_results']; ?></p>
        <?php else: ?>
            <p><?php echo $lang_texts['no_lists_yet']; ?></p>
            <a href="index.php?view=lists/create" class="btn"><?php echo $lang_texts['create_first_list']; ?></a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="lists-container">
        <?php foreach ($lists as $list): ?>
        <div class="list-card">
            <h2 class="list-title"><?php echo htmlspecialchars($list['title']); ?></h2>
            
            <div class="list-meta">
                <span>
                    <?php 
                    // Отримання кількості виконаних елементів
                    $completedItems = 0;
                    $totalItems = 0;
                    
                    if (isset($list['items'])) {
                        $totalItems = count($list['items']);
                        foreach($list['items'] as $item) {
                            if ($item['completed']) {
                                $completedItems++;
                            }
                        }
                    }
                    
                    echo $completedItems . '/' . $totalItems . ' ' . $lang_texts['completed'];
                    ?>
                </span>
                <span><?php echo date('d.m.Y', strtotime($list['created_at'])); ?></span>
            </div>
            
            <?php if (isset($list['items']) && !empty($list['items'])): ?>
            <ul class="list-items-preview">
                <?php 
                // Відображати лише перші 3 елементи списку
                $previewItems = array_slice($list['items'], 0, 3);
                foreach($previewItems as $item): 
                ?>
                <li class="<?php echo $item['completed'] ? 'list-item-complete' : ''; ?>">
                    <?php echo htmlspecialchars($item['text']); ?>
                </li>
                <?php endforeach; ?>
                
                <?php if (count($list['items']) > 3): ?>
                <li class="more-items">
                    <?php echo sprintf($lang_texts['more_items'], (count($list['items']) - 3)); ?>
                </li>
                <?php endif; ?>
            </ul>
            <?php else: ?>
            <p class="empty-list-message"><?php echo $lang_texts['empty_list']; ?></p>
            <?php endif; ?>
            
            <div class="list-actions">
                <a href="index.php?view=lists/view&id=<?php echo $list['id']; ?>" class="btn"><?php echo $lang_texts['view']; ?></a>
                <a href="index.php?view=lists/edit&id=<?php echo $list['id']; ?>" class="btn btn-secondary"><?php echo $lang_texts['edit']; ?></a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script src="assets/js/main.js"></script>

<?php include 'includes/footer.php'; ?>