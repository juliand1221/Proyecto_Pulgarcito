<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            🏫 Jardín Pulgarcito - Administrador
        </a>
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            <a href="../logout.php" class="btn btn-outline-light">Cerrar sesión</a>
        </div>
    </div>
</nav>
