<?php
// Контролер для роботи зі списками

class ListController {
    private $db;
    
    public function __construct() {
        // Підключення до бази даних
        require_once 'config/init.php';
        global $db;
        $this->db = $db;
    }
    
    // Отримання всіх списків користувача
    public function getUserLists($userId) {
        $query = "SELECT id, title, created_at, updated_at FROM lists WHERE user_id = ? ORDER BY updated_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $lists = [];
        while ($row = $result->fetch_assoc()) {
            // Додаємо елементи списку до кожного списку
            $row['items'] = $this->getListItems($row['id']);
            $lists[] = $row;
        }
        
        return $lists;
    }
    
    // Отримання елементів одного списку
    public function getListItems($listId) {
        $query = "SELECT id, text, completed FROM list_items WHERE list_id = ? ORDER BY id ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $listId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        return $items;
    }
    
    // Пошук списків за назвою
    public function searchLists($userId, $searchTerm) {
        $searchTerm = "%$searchTerm%";
        $query = "SELECT id, title, created_at, updated_at FROM lists WHERE user_id = ? AND title LIKE ? ORDER BY updated_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("is", $userId, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $lists = [];
        while ($row = $result->fetch_assoc()) {
            // Додаємо елементи списку до кожного списку
            $row['items'] = $this->getListItems($row['id']);
            $lists[] = $row;
        }
        
        return $lists;
    }
    
    // Отримання одного списку за ID
    public function getList($listId, $userId) {
        $query = "SELECT id, title, created_at, updated_at FROM lists WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $listId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $list = $result->fetch_assoc();
        $list['items'] = $this->getListItems($listId);
        
        return $list;
    }
    
    // Створення нового списку
    public function createList($userId, $title) {
        $query = "INSERT INTO lists (user_id, title, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("is", $userId, $title);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }
    
    // Додавання елементу до списку
    public function addItem($listId, $text) {
        $query = "INSERT INTO list_items (list_id, text, completed) VALUES (?, ?, 0)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("is", $listId, $text);
        
        if ($stmt->execute()) {
            // Оновлюємо дату останньої зміни списку
            $this->updateListTimestamp($listId);
            return $this->db->insert_id;
        }
        
        return false;
    }
    
    // Зміна статусу елемента (виконано/не виконано)
    public function toggleItemComplete($itemId) {
        // Спочатку отримуємо поточний статус елемента та ID списку
        $query = "SELECT list_id, completed FROM list_items WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $item = $result->fetch_assoc();
        $listId = $item['list_id'];
        $currentStatus = $item['completed'];
        $newStatus = $currentStatus ? 0 : 1;
        
        // Змінюємо статус елемента
        $query = "UPDATE list_items SET completed = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $newStatus, $itemId);
        
        if ($stmt->execute()) {
            // Оновлюємо дату останньої зміни списку
            $this->updateListTimestamp($listId);
            return $newStatus;
        }
        
        return false;
    }
    
    // Видалення елементу списку
    public function deleteItem($itemId) {
        // Спочатку отримуємо ID списку
        $query = "SELECT list_id FROM list_items WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $item = $result->fetch_assoc();
        $listId = $item['list_id'];
        
        // Видаляємо елемент
        $query = "DELETE FROM list_items WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $itemId);
        
        if ($stmt->execute()) {
            // Оновлюємо дату останньої зміни списку
            $this->updateListTimestamp($listId);
            return true;
        }
        
        return false;
    }
    
    // Зміна назви списку
    public function renameList($listId, $userId, $title) {
        $query = "UPDATE lists SET title = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sii", $title, $listId, $userId);
        
        return $stmt->execute();
    }
    
    // Видалення списку разом з його елементами
    public function deleteList($listId, $userId) {
        // Перевіряємо, чи список належить користувачу
        $query = "SELECT id FROM lists WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $listId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        // Починаємо транзакцію
        $this->db->begin_transaction();
        
        try {
            // Видаляємо елементи списку
            $query = "DELETE FROM list_items WHERE list_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $listId);
            $stmt->execute();
            
            // Видаляємо сам список
            $query = "DELETE FROM lists WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $listId);
            $stmt->execute();
            
            // Підтверджуємо транзакцію
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            // Відкатуємо зміни у випадку помилки
            $this->db->rollback();
            return false;
        }
    }
    
    // Оновлення дати останньої зміни списку
    private function updateListTimestamp($listId) {
        $query = "UPDATE lists SET updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $listId);
        return $stmt->execute();
    }
    
    // Обробка AJAX запитів
    public function handleAjaxRequests() {
        // Перевірка авторизації користувача
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Користувач не авторизований']);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        
        switch ($action) {
            case 'addItem':
                if (isset($_POST['listId']) && isset($_POST['itemText'])) {
                    $listId = (int)$_POST['listId'];
                    $itemText = $_POST['itemText'];
                    
                    // Перевіряємо, чи список належить користувачу
                    $list = $this->getList($listId, $userId);
                    if ($list) {
                        $itemId = $this->addItem($listId, $itemText);
                        if ($itemId) {
                            echo json_encode(['success' => true, 'itemId' => $itemId]);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Помилка при додаванні елементу']);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Список не знайдено']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Відсутні обов\'язкові параметри']);
                }
                break;
                
            case 'toggleComplete':
                if (isset($_POST['itemId'])) {
                    $itemId = (int)$_POST['itemId'];
                    $completed = $this->toggleItemComplete($itemId);
                    if ($completed !== false) {
                        echo json_encode(['success' => true, 'completed' => (bool)$completed]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Помилка при зміні статусу елементу']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Відсутній ID елементу']);
                }
                break;
                
            case 'deleteItem':
                if (isset($_POST['itemId'])) {
                    $itemId = (int)$_POST['itemId'];
                    if ($this->deleteItem($itemId)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Помилка при видаленні елементу']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Відсутній ID елементу']);
                }
                break;
                
            case 'renameList':
                if (isset($_POST['listId']) && isset($_POST['title'])) {
                    $listId = (int)$_POST['listId'];
                    $title = $_POST['title'];
                    if ($this->renameList($listId, $userId, $title)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Помилка при перейменуванні списку']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Відсутні обов\'язкові параметри']);
                }
                break;
                
            case 'deleteList':
                if (isset($_POST['listId'])) {
                    $listId = (int)$_POST['listId'];
                    if ($this->deleteList($listId, $userId)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Помилка при видаленні списку']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Відсутній ID списку']);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Невідома дія']);
                break;
        }
        
        exit;
    }
}

// Обробка AJAX запитів, якщо скрипт викликається безпосередньо
if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) {
    session_start();
    $controller = new ListController();
    $controller->handleAjaxRequests();
}
?>