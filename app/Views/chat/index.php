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
                <div class="conv-item <?= (($activeConversation['id'] ?? 0) == $conv['id']) ? 'active' : '' ?>">
                    <a href="/chat?id=<?= (int) $conv['id'] ?>" class="conv-link"><?= e($conv['title']) ?></a>
                    <form method="post" action="/chat/rename" class="conv-actions">
                        <?= csrf_field() ?>
                        <input type="hidden" name="conversation_id" value="<?= (int) $conv['id'] ?>">
                        <input type="text" name="title" value="<?= e($conv['title']) ?>" maxlength="180" required>
                        <button type="submit">Renombrar</button>
                    </form>
                    <form method="post" action="/chat/delete" class="conv-actions">
                        <?= csrf_field() ?>
                        <input type="hidden" name="conversation_id" value="<?= (int) $conv['id'] ?>">
                        <button type="submit" class="danger">Borrar</button>
                    </form>
                </div>
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
            <small>OpenRouter integrado (normal + streaming opcional).</small>
        </header>
        <div class="messages" id="messages">
            <?php if ($error = flash_get('error')): ?>
                <div class="alert"><?= e($error) ?></div>
            <?php endif; ?>
            <?php foreach ($messages as $message): ?>
                <article class="msg <?= e($message['sender']) ?>">
                    <strong><?= $message['sender'] === 'assistant' ? 'Asistente IA' : 'Abogado' ?></strong>
                    <div class="msg-md"><?= safe_markdown((string) $message['content']) ?></div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($activeConversation): ?>
        <form method="post" action="/chat/message" class="composer">
            <?= csrf_field() ?>
            <input type="hidden" name="conversation_id" value="<?= (int) $activeConversation['id'] ?>">
            <textarea name="content" rows="3" placeholder="Escribe tu consulta legal..." required></textarea>
            <label style="display:flex;gap:8px;align-items:center;">
                <input type="checkbox" name="stream_mode" value="1"> Streaming opcional (OpenRouter)
            </label>
            <button type="submit">Enviar</button>
        </form>
        <?php endif; ?>
    </section>
</div>
