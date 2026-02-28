<main class="center-card">
    <section class="card wide">
        <h2>Panel ADMIN</h2>
        <p>Configura marca, IA, usuarios y revisa auditoría/consumo.</p>

        <?php if ($error = flash_get('error')): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success = flash_get('success')): ?>
            <div class="alert" style="background:#1f4d30;"><?= e($success) ?></div>
        <?php endif; ?>

        <h3>Marca</h3>
        <form method="post" action="/admin/brand" class="form-grid two-cols">
            <?= csrf_field() ?>
            <label>Nombre</label><input name="brand_name" value="<?= e((string) $brand['name']) ?>" required>
            <label>URL Logo</label><input name="brand_logo" value="<?= e((string) $brand['logo']) ?>">
            <label>Color primario</label><input name="brand_color_primary" value="<?= e((string) $brand['primary']) ?>" required>
            <label>Color secundario</label><input name="brand_color_secondary" value="<?= e((string) $brand['secondary']) ?>" required>
            <button type="submit">Guardar marca</button>
        </form>

        <h3>IA OpenRouter</h3>
        <form method="post" action="/admin/ai" class="form-grid">
            <?= csrf_field() ?>
            <label>Modelo</label>
            <input name="ai_model" value="<?= e((string) $ai['model']) ?>" required>
            <label>Temperatura</label>
            <input name="ai_temperature" value="<?= e((string) $ai['temperature']) ?>" required>
            <label>Max tokens</label>
            <input name="ai_max_tokens" value="<?= e((string) $ai['max_tokens']) ?>" required>
            <label>System prompt</label>
            <textarea name="system_prompt" rows="12" required><?= e((string) $ai['system_prompt']) ?></textarea>
            <button type="submit">Guardar IA</button>
        </form>

        <h3>CRUD Usuarios</h3>
        <form method="post" action="/admin/users/create" class="form-grid two-cols">
            <?= csrf_field() ?>
            <label>Nombre</label><input name="name" required>
            <label>Email</label><input name="email" type="email" required>
            <label>Password</label><input name="password" type="password" required>
            <label>Rol</label>
            <select name="role">
                <option value="USER">USER</option>
                <option value="ADMIN">ADMIN</option>
            </select>
            <button type="submit">Crear usuario</button>
        </form>

        <div style="overflow:auto; margin-top:12px;">
            <table style="width:100%; border-collapse: collapse;">
                <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= (int) $u['id'] ?></td>
                        <td><?= e($u['name']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= e($u['role']) ?></td>
                        <td><?= e($u['status']) ?></td>
                        <td>
                            <form method="post" action="/admin/users/toggle-status" style="display:inline-flex;gap:6px;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                <input type="hidden" name="status" value="<?= $u['status'] === 'active' ? 'inactive' : 'active' ?>">
                                <button type="submit"><?= $u['status'] === 'active' ? 'Desactivar' : 'Activar' ?></button>
                            </form>
                            <form method="post" action="/admin/users/reset-password" style="display:inline-flex;gap:6px;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                <input type="password" name="new_password" placeholder="Nueva pass" required>
                                <button type="submit">Reset pass</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h3>Audit Logs</h3>
        <div style="max-height:240px;overflow:auto;background:#0e1530;padding:10px;border-radius:8px;">
            <?php foreach ($auditLogs as $log): ?>
                <div style="padding:6px 0;border-bottom:1px solid #253364;">
                    <strong><?= e((string) $log['action']) ?></strong>
                    <small>user:<?= e((string) ($log['user_id'] ?? '-')) ?> | <?= e((string) $log['created_at']) ?></small>
                </div>
            <?php endforeach; ?>
        </div>

        <h3>API Usage Logs</h3>
        <div style="max-height:240px;overflow:auto;background:#0e1530;padding:10px;border-radius:8px;">
            <?php foreach ($usageLogs as $log): ?>
                <div style="padding:6px 0;border-bottom:1px solid #253364;">
                    <strong><?= e((string) ($log['model'] ?? '-')) ?></strong>
                    <small>status:<?= e((string) ($log['http_status'] ?? '-')) ?> | latency:<?= e((string) ($log['latency_ms'] ?? '-')) ?>ms | tokens:<?= e((string) ($log['total_tokens_est'] ?? '-')) ?></small>
                </div>
            <?php endforeach; ?>
        </div>

        <a class="btn" href="/chat">Ir al chat</a>
    </section>
</main>
