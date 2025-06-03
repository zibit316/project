<?php
// models/List.php
// Модель для роботи зі списками

class ListModel {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Створення нового списку
    public function create($userId, $title, $description = '', $isPublic = false) {
        $stmt = $this->db->prepare("INSERT INTO lists (user_id, title, description, is_public) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $userId, $title, $description, $isPublic);
        
        if ($stmt->execute()) {
            return ['success' => true, 'list_id' => $stmt->insert_id, 'message' => __('list_created')];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }
    
    // Отримання списку за ID
    public function getById($listId) {
        $stmt = $this->db->prepare("SELECT l.*, u.username as owner_name 
                                   FROM lists l
                                   JOIN users u ON l.user_id = u.id
                                   WHERE l.id = ?");
        $stmt->bind_param("i", $listId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Оновлення списку
    public function update($listId, $title, $description, $isPublic) {
        $stmt = $this->db->prepare("UPDATE lists SET title = ?, description = ?, is_public = ? WHERE id = ?");
        $stmt->bind_param("ssii", $title, $description, $isPublic, $listId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => __('list_updated')];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }
    
    // Видалення списку
    public function delete($listId) {
        $stmt = $this->db->prepare("DELETE FROM lists WHERE id = ?");
        $stmt->bind_param("i", $listId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => __('list_deleted')];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }
    
    // Отримання всіх списків користувача
    public function getAllByUser($userId) {
        $stmt = $this->db->prepare("SELECT l.*, 
                                  (SELECT COUNT(*) FROM items WHERE list_id = l.id) AS total_items,
                                  (SELECT COUNT(*) FROM items WHERE list_id = l.id AND is_completed = 1) AS completed_items
                                  FROM lists l
                                  WHERE l.user_id = ?
                                  ORDER BY l.updated_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $lists = [];
        
        while ($row = $result->fetch_assoc()) {
            $lists[] = $row;
        }
        
        return $lists;
    }
    
    // Отримання списків, доступних для користувача через спільний доступ
    public function getSharedWithUser($userId) {
        $stmt = $this->db->prepare("SELECT l.*, u.username as owner_name, sl.can_edit,
                                  (SELECT COUNT(*) FROM items WHERE list_id = l.id) AS total_items,
                                  (SELECT COUNT(*) FROM items WHERE list_id = l.id AND is_completed = 1) AS completed_items
                                  FROM lists l
                                  JOIN shared_lists sl ON l.id = sl.list_id
                                  JOIN users u ON l.user_id = u.id
                                  WHERE sl.user_id = ?
                                  ORDER BY l.updated_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $lists = [];
        
        while ($row = $result->fetch_assoc()) {
            $lists[] = $row;
        }
        
        return $lists;
    }
    
    // Перевірка чи має користувач доступ до списку (власник або спільний доступ)
    public function canUserAccess($listId, $userId) {
        // Перевірка власності
        $stmt = $this->db->prepare("SELECT id FROM lists WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $listId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return ['access' => true, 'is_owner' => true, 'can_edit' => true];
        }
        
        // Перевірка спільного доступу
        $stmt = $this->db->prepare("SELECT can_edit FROM shared_lists WHERE list_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $listId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return ['access' => true, 'is_owner' => false, 'can_edit' => (bool)$row['can_edit']];
        }
        
        // Перевірка публічного доступу
        $stmt = $this->db->prepare("SELECT is_public FROM lists WHERE id = ?");
        $stmt->bind_param("i", $listId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['is_public']) {
                return ['access' => true, 'is_owner' => false, 'can_edit' => false];
            }
        }
        
        return ['access' => false];
    }
    
    // Надання спільного доступу до списку
    public function shareList($listId, $ownerUserId, $targetEmail, $canEdit = false) {
        // Перевірка чи є користувач власником списку
        $stmt = $this->db->prepare("SELECT id FROM lists WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $listId, $ownerUserId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            return ['success' => false, 'message' => __('not_authorized')];
        }
        
        // Знайти користувача за email
        $userModel = new User($this->db);
        $targetUser = $userModel->findUserByEmail($targetEmail);
        
        if (!$targetUser) {
            return ['success' => false, 'message' => __('user_not_found')];
        }
        
        // Перевірка чи вже надано доступ
        $stmt = $this->db->prepare("SELECT id FROM shared_lists WHERE list_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $listId, $targetUser['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Оновлення рівня доступу
            $stmt = $this->db->prepare("UPDATE shared_lists SET can_edit = ? WHERE list_id = ? AND user_id = ?");
            $stmt->bind_param("iii", $canEdit, $listId, $targetUser['id']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => __('share_success')];
            } else {
                return ['success' => false, 'message' => $stmt->error];
            }
        }
        
        // Додавання спільного доступу
        $stmt = $this->db->prepare("INSERT INTO shared_lists (list_id, user_id, can_edit) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $listId, $targetUser['id'], $canEdit);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => __('share_success')];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }
    
    // Видалення спільного доступу
    public function removeSharing($listId, $ownerUserId, $targetUserId) {
        // Перевірка чи є користувач власником списку
        $stmt = $this->db->prepare("SELECT id FROM lists WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $listId, $ownerUserId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            return ['success' => false, 'message' => __('not_authorized')];
        }
        
        // Видалення спільного доступу
        $stmt = $this->db->prepare("DELETE FROM shared_lists WHERE list_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $listId, $targetUserId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => __('share_removed')];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }
    
    // Отримання користувачів, з якими поділені списком
    public function getSharedUsers($listId) {
        $stmt = $this->db->prepare("SELECT u.id, u.username, u.email, sl.can_edit
                                   FROM shared_lists sl
                                   JOIN users u ON sl.user_id = u.id
                                   WHERE sl.list_id = ?");
        $stmt->bind_param("i", $listId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $users = [];
        
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    }
}