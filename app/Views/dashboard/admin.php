<main class="center-card">
    <section class="card">
        <h2>Panel ADMIN</h2>
        <p>Hola, <?= e($user['name'] ?? '') ?>. Este espacio está protegido por RoleGuard (ADMIN).</p>
        <a class="btn" href="/chat">Ir al chat</a>
    </section>
</main>
