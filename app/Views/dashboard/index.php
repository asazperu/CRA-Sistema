<main class="center-card">
    <section class="card">
        <h2>Bienvenido(a), <?= e($user['name'] ?? 'Usuario') ?></h2>
        <p>Use el asistente legal para consultas rápidas de estrategia procesal peruana.</p>
        <a class="btn" href="/chat">Abrir Asistente IA</a>
        <form method="post" action="/logout">
            <?= csrf_field() ?>
            <button class="btn-link" type="submit">Cerrar sesión</button>
        </form>
    </section>
</main>
