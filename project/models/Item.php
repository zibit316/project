<?php
// models/Item.php
// Модель для роботи з елементами списків

class Item {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Додавання нового елементу до списку
    public function create($listId, $content) {
        // Отримання найбільшої позиції в списку
        $stmt = $this->db->prepare("SELECT MAX(position) as max_pos FROM items WHERE list_id = ?");
        $stmt->bind_param("i", $listId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $position = $row['max_pos'] ? $row['max_pos'] + 1 : 1;
        
        // Додавання нового елементу
        $stmt = $this->db->prepare("INSERT INTO items (list_id, content, position) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $listId, $content, $position);
        
        if ($stmt->execute()) {
            return [
                'success' => true, 
                'item_id' => $stmt->insert_id, 
                'message' => __('item_created'),
                'item' => [
                    'id' => $stmt->insert_id,
                    'content' => $content,
                    'is_completed' => 0,
                    'position' => $position,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }
    
    // Отримання елементів списку
    public function getByListId($listId) {
        $stmt = $this->db->prepare("SELECT * FROM items WHERE list_id = ? ORDER BY position ASC");
        $stmt->bind_param("i", $listId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $items = [];
        
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        return $items;
    }
    
    // Оновлення елементу
    public function update($itemId, $content) {
        $stmt = $this->db->prepare("UPDATE items SET content = ? WHERE id = ?");
        $stmt->bind_param("si", $content, $itemId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => __('item_updated')];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }
    
    // Видалення елементу
    public function delete($itemId) {
        // Отримання інформації про елемент перед видаленням
        $stmt = $this->db->prepare("SELECT list_id, position FROM items WHERE id = ?");
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            return ['success' => false, 'message' => __('item_not_found')];
        }
        
        $item = $result->fetch_assoc();
        $listId = $item['list_id'];
        $position = $item['position'];
        
        // Видалення елементу
        $stmt = $this->db->prepare("DELETE FROM items WHERE id = ?");
        $stmt->bind_param("i", $itemId);
        
        if (!$stmt->execute()) {
            return ['success' => false, 'message' => $stmt->error];
        }
        
        // Оновлення позицій інших елементів
        $stmt = $this->db->prepare("UPDATE items SET position = position - 1 WHERE list_id = ? AND position > ?");
        $stmt->bind_param("ii", $listId, $position);
        $stmt->execute();
        
        return ['success' => true, 'message' => __('item_deleted')];
    }
    
    // Позначення елементу як виконаного/невиконаного
    public function toggleCompleted($itemId) {
        $stmt = $this->db->prepare("UPDATE items SET is_completed = NOT is_completed WHERE id = ?");
        $stmt->bind_param("i", $itemId);
        
        if ($stmt->execute()) {
            // Отримання оновленого стану
            $stmt = $this->db->prepare("SELECT is_completed FROM items WHERE id = ?");
            $stmt->bind_param("i", $itemId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $item = $result->fetch_assoc();
                $message = $item['is_completed'] ? __('mark_completed') : __('mark_incomplete');
                return ['success' => true, 'message' => $message, 'is_completed' => (bool)$item['is_completed']];
            }
            
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }
    
    // Оновлення позицій елементів (перетягування)
    public function updatePositions($items) {
        if (empty($items) || !is_array($items)) {
            return ['success' => false, 'message' => __('invalid_request')];
        }
        
        $this->db->begin_transaction();
        
        try {
            foreach ($items as $position => $itemId) {
                $stmt = $this->db->prepare("UPDATE items SET position = ? WHERE id = ?");
                $pos = $position + 1; // Позиції починаються з 1
                $stmt->bind_param("ii", $pos, $itemId);
                $stmt->execute();
            }
            
            $this->db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Отримання елементу за ID
    public function getById($itemId) {
        $stmt = $this->db->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
}