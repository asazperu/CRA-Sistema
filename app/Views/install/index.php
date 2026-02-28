<main class="center-card">
    <section class="card wide">
        <h1>Instalador de Castro Romero Abogados</h1>
        <p>Este instalador solicita una BD ya creada en cPanel/phpMyAdmin, prueba conexión, importa <code>database.sql</code>, crea ADMIN inicial, guarda <code>.env</code> y bloquea con <code>install.lock</code>.</p>

        <?php if ($error = flash_get('error')): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success = flash_get('success')): ?>
            <div class="alert" style="background:#1f4d30;"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post" action="/install" class="form-grid two-cols">
            <?= csrf_field() ?>
            <label>DB Host</label><input name="db_host" value="<?= e((string) old('db_host', 'localhost')) ?>" required>
            <label>DB Port</label><input name="db_port" value="<?= e((string) old('db_port', '3306')) ?>" required>
            <label>DB Name (ya creada)</label><input name="db_name" value="<?= e((string) old('db_name')) ?>" required>
            <label>DB User</label><input name="db_user" value="<?= e((string) old('db_user')) ?>" required>
            <label>DB Password</label><input name="db_pass" type="password">
            <label>URL App</label><input name="app_url" value="<?= e((string) old('app_url')) ?>" placeholder="https://tu-dominio.com" required>
            <label>Admin Nombre</label><input name="admin_name" value="<?= e((string) old('admin_name')) ?>" required>
            <label>Admin Email</label><input name="admin_email" type="email" value="<?= e((string) old('admin_email')) ?>" required>
            <label>Admin Password</label><input name="admin_pass" type="password" required>

            <button type="submit" formaction="/install/test-connection">Probar conexión MySQL</button>
            <button type="submit">Instalar ahora</button>
        </form>
    </section>
</main>
