<main class="center-card">
    <section class="card">
        <?php $brandName = app_setting('brand_name', 'Castro Romero Abogados'); ?>
        <?php $brandLogo = app_setting('brand_logo', ''); ?>
        <?php if ($brandLogo !== ''): ?>
            <img src="<?= e($brandLogo) ?>" alt="Logo" style="max-height:52px;margin-bottom:10px;">
        <?php endif; ?>
        <h1><?= e($brandName) ?></h1>
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
