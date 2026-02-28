<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $brandName = app_setting('brand_name', 'Castro Romero Abogados'); ?>
    <?php $brandPrimary = app_setting('brand_color_primary', '#4f7cff'); ?>
    <?php $brandSecondary = app_setting('brand_color_secondary', '#1f2a50'); ?>
    <title><?= e($title ?? $brandName) ?></title>
    <style>:root{--accent:<?= e($brandPrimary) ?>;--assistant:<?= e($brandSecondary) ?>}</style>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<?= $content ?>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
