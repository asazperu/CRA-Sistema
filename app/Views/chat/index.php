<div class="chat-layout">
    <aside class="sidebar">
        <h2>CRA Legal IA</h2>
        <form method="post" action="/chat/new" class="new-chat-form">
            <?= csrf_field() ?>
            <input type="text" name="title" placeholder="Nueva conversación" required>
            <button type="submit">+ Nuevo</button>
        </form>
        <nav>
            <?php foreach ($conversations as $conv): ?>
                <a href="/chat?id=<?= (int) $conv['id'] ?>" class="conv-link <?= (($activeConversation['id'] ?? 0) == $conv['id']) ? 'active' : '' ?>"><?= e($conv['title']) ?></a>
            <?php endforeach; ?>
        </nav>
        <form method="post" action="/logout">
            <?= csrf_field() ?>
            <button type="submit" class="logout">Salir</button>
        </form>
    </aside>
    <section class="chat-main">
        <header>
            <h1><?= e($activeConversation['title'] ?? 'Nueva consulta legal') ?></h1>
            <small>Asistente especializado en contexto jurídico peruano.</small>
        </header>
        <div class="messages" id="messages">
            <?php if ($error = flash_get('error')): ?>
                <div class="alert"><?= e($error) ?></div>
            <?php endif; ?>
            <?php foreach ($messages as $message): ?>
                <article class="msg <?= e($message['sender']) ?>">
                    <strong><?= $message['sender'] === 'assistant' ? 'Asistente IA' : 'Abogado' ?></strong>
                    <p><?= nl2br(e($message['content'])) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($activeConversation): ?>
        <form method="post" action="/chat/message" class="composer">
            <?= csrf_field() ?>
            <input type="hidden" name="conversation_id" value="<?= (int) $activeConversation['id'] ?>">
            <textarea name="content" rows="3" placeholder="Escribe tu consulta legal..." required></textarea>
            <button type="submit">Enviar</button>
        </form>
        <?php endif; ?>
    </section>
</div>
