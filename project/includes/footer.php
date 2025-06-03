<?php
// footer.php - Підвал сайту
?>
<footer class="footer mt-auto py-3 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>Про проект</h5>
                <p>Сервіс для створення зручних списків покупок та завдань з українською локалізацією.</p>
            </div>
            <div class="col-md-3">
                <h5>Навігація</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php">Головна</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php">Мої списки</a></li>
                        <li><a href="logout.php">Вийти</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Увійти</a></li>
                        <li><a href="register.php">Зареєструватись</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Мова</h5>
                <ul class="list-unstyled">
                    <li><a href="?lang=ua">Українська</a></li>
                    <li><a href="?lang=en">English</a></li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p>&copy; <?php echo date('Y'); ?> - Веб-сайт для створення списків. Всі права захищено.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS та інші скрипти -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>