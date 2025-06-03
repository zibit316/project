// Очікувати завантаження DOM
document.addEventListener('DOMContentLoaded', function() {
    // Функціонал для створення нових елементів списку
    const addItemForm = document.getElementById('addItemForm');
    if (addItemForm) {
        addItemForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const itemInput = document.getElementById('newItem');
            const itemText = itemInput.value.trim();
            
            if (itemText !== '') {
                addItemToList(itemText);
                itemInput.value = '';
            }
        });
    }

    // Функція додавання нового елемента до списку
    function addItemToList(text) {
        const listId = document.getElementById('listId').value;
        
        // AJAX запит для додавання елементу до списку
        fetch('controllers/lists.php?action=addItem', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `listId=${listId}&itemText=${encodeURIComponent(text)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Додаємо елемент до DOM
                const itemsList = document.getElementById('itemsList');
                const li = document.createElement('li');
                li.className = 'list-item';
                li.dataset.id = data.itemId;
                
                const itemContent = document.createElement('span');
                itemContent.textContent = text;
                
                const actions = document.createElement('div');
                actions.className = 'item-actions';
                
                const completeBtn = document.createElement('button');
                completeBtn.className = 'btn-action complete-item';
                completeBtn.innerHTML = '<i class="fas fa-check"></i>';
                completeBtn.addEventListener('click', function() {
                    toggleItemComplete(data.itemId);
                });
                
                const deleteBtn = document.createElement('button');
                deleteBtn.className = 'btn-action delete-item';
                deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
                deleteBtn.addEventListener('click', function() {
                    deleteItem(data.itemId);
                });
                
                actions.appendChild(completeBtn);
                actions.appendChild(deleteBtn);
                
                li.appendChild(itemContent);
                li.appendChild(actions);
                itemsList.appendChild(li);
            } else {
                showAlert('Помилка при додаванні елементу списку', 'error');
            }
        })
        .catch(error => {
            console.error('Помилка:', error);
            showAlert('Сталася помилка при спробі додати елемент', 'error');
        });
    }
    
    // Позначення елементу списку як виконаного
    function toggleItemComplete(itemId) {
        fetch('controllers/lists.php?action=toggleComplete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `itemId=${itemId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`li[data-id="${itemId}"]`);
                if (data.completed) {
                    item.classList.add('list-item-complete');
                } else {
                    item.classList.remove('list-item-complete');
                }
            }
        })
        .catch(error => {
            console.error('Помилка:', error);
            showAlert('Сталася помилка при зміні статусу елементу', 'error');
        });
    }
    
    // Видалення елементу списку
    function deleteItem(itemId) {
        if (confirm('Ви впевнені, що хочете видалити цей елемент списку?')) {
            fetch('controllers/lists.php?action=deleteItem', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `itemId=${itemId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = document.querySelector(`li[data-id="${itemId}"]`);
                    item.remove();
                    showAlert('Елемент успішно видалено', 'success');
                }
            })
            .catch(error => {
                console.error('Помилка:', error);
                showAlert('Сталася помилка при видаленні елементу', 'error');
            });
        }
    }
    
    // Функція для перейменування списку
    const renameListForm = document.getElementById('renameListForm');
    if (renameListForm) {
        renameListForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const listId = document.getElementById('listId').value;
            const newTitle = document.getElementById('newListTitle').value.trim();
            
            if (newTitle !== '') {
                fetch('controllers/lists.php?action=renameList', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `listId=${listId}&title=${encodeURIComponent(newTitle)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('listTitle').textContent = newTitle;
                        document.getElementById('newListTitle').value = '';
                        showAlert('Назву списку успішно змінено', 'success');
                    }
                })
                .catch(error => {
                    console.error('Помилка:', error);
                    showAlert('Сталася помилка при перейменуванні списку', 'error');
                });
            }
        });
    }
    
    // Функція для відображення повідомлень
    function showAlert(message, type) {
        const alertsContainer = document.getElementById('alerts');
        if (!alertsContainer) return;
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        
        alertsContainer.appendChild(alert);
        
        // Автоматичне видалення повідомлення через 3 секунди
        setTimeout(() => {
            alert.remove();
        }, 3000);
    }
    
    // Обробники подій для вже існуючих елементів списку
    document.querySelectorAll('.complete-item').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.closest('.list-item').dataset.id;
            toggleItemComplete(itemId);
        });
    });
    
    document.querySelectorAll('.delete-item').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.closest('.list-item').dataset.id;
            deleteItem(itemId);
        });
    });
    
    // Обробник події для видалення списку
    const deleteListBtn = document.getElementById('deleteList');
    if (deleteListBtn) {
        deleteListBtn.addEventListener('click', function() {
            if (confirm('Ви впевнені, що хочете видалити цей список?')) {
                const listId = document.getElementById('listId').value;
                
                fetch('controllers/lists.php?action=deleteList', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `listId=${listId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'dashboard.php';
                    }
                })
                .catch(error => {
                    console.error('Помилка:', error);
                    showAlert('Сталася помилка при видаленні списку', 'error');
                });
            }
        });
    }
    
    // Функціонал пошуку списків
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = document.getElementById('searchTerm').value.trim();
            window.location.href = `index.php?search=${encodeURIComponent(searchTerm)}`;
        });
    }
    
    // Зміна мови інтерфейсу
    const langSelect = document.getElementById('languageSelect');
    if (langSelect) {
        langSelect.addEventListener('change', function() {
            const selectedLang = this.value;
            document.cookie = `lang=${selectedLang}; path=/; max-age=31536000`;
            window.location.reload();
        });
    }
});