<main class="center-card">
    <section class="card">
        <h2>Bienvenido(a), <?= e($user['name'] ?? 'Usuario') ?></h2>
        <p>Rol: <?= e($user['role'] ?? 'USER') ?></p>
        <p>Use el asistente legal para consultas rápidas de estrategia procesal peruana.</p>
        <a class="btn" href="/chat">Abrir Asistente IA</a>
        <a class="btn" href="/documentos">Módulo Documentos</a>
        <a class="btn" href="/password/change">Cambiar contraseña</a>
        <?php if (($user['role'] ?? '') === 'ADMIN'): ?>
            <a class="btn" href="/admin">Panel Admin</a>
        <?php endif; ?>
        <form method="post" action="/logout">
            <?= csrf_field() ?>
            <button class="btn-link" type="submit">Cerrar sesión</button>
        </form>
    </section>
</main>
