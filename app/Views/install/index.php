<main class="center-card">
    <section class="card wide">
        <h1>Instalador de Castro Romero Abogados</h1>
        <p>Este asistente importará <code>database.sql</code>, creará el usuario admin y dejará bloqueada la instalación con <code>install.lock</code>.</p>
        <?php if ($error = flash_get('error')): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/install" class="form-grid two-cols">
            <?= csrf_field() ?>
            <label>DB Host</label><input name="db_host" value="localhost" required>
            <label>DB Port</label><input name="db_port" value="3306" required>
            <label>DB Name</label><input name="db_name" required>
            <label>DB User</label><input name="db_user" required>
            <label>DB Password</label><input name="db_pass" type="password">
            <label>URL App</label><input name="app_url" placeholder="https://tu-dominio.com" required>
            <label>Admin Nombre</label><input name="admin_name" required>
            <label>Admin Email</label><input name="admin_email" type="email" required>
            <label>Admin Password</label><input name="admin_pass" type="password" required>
            <button type="submit">Instalar ahora</button>
        </form>
    </section>
</main>
