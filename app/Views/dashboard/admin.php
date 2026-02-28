<main class="center-card">
    <section class="card wide">
        <h2>Panel ADMIN</h2>
        <p>Hola, <?= e($user['name'] ?? '') ?>. Configura el system prompt del agente.</p>

        <?php if ($error = flash_get('error')): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success = flash_get('success')): ?>
            <div class="alert" style="background:#1f4d30;"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/system-prompt" class="form-grid">
            <?= csrf_field() ?>
            <label>System prompt OpenRouter (configurable)</label>
            <textarea name="system_prompt" rows="14" required><?= e((string) $systemPrompt) ?></textarea>
            <button type="submit">Guardar prompt</button>
        </form>

        <a class="btn" href="/chat">Ir al chat</a>
    </section>
</main>
