<main class="center-card">
    <section class="card">
        <h1>Cambiar contraseña</h1>
        <?php if ($error = flash_get('error')): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success = flash_get('success')): ?>
            <div class="alert"><?= e($success) ?></div>
        <?php endif; ?>
        <form method="post" action="/password/change" class="form-grid">
            <?= csrf_field() ?>
            <label>Contraseña actual</label>
            <input type="password" name="current_password" required>
            <label>Nueva contraseña</label>
            <input type="password" name="new_password" minlength="8" required>
            <label>Confirmar nueva contraseña</label>
            <input type="password" name="confirm_password" minlength="8" required>
            <button type="submit">Actualizar</button>
        </form>
    </section>
</main>
