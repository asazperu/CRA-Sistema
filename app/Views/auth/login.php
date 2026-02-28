<main class="center-card">
    <section class="card">
        <h1>Castro Romero Abogados</h1>
        <p>Sistema legal tipo ChatGPT/Gemini para abogados peruanos.</p>
        <?php if ($error = flash_get('error')): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/login" class="form-grid">
            <?= csrf_field() ?>
            <label>Email</label>
            <input type="email" name="email" value="<?= e((string) old('email')) ?>" required>
            <label>Contraseña</label>
            <input type="password" name="password" required>
            <button type="submit">Ingresar</button>
        </form>
    </section>
</main>
