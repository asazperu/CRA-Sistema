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
        <a class="btn" href="/documentos" style="width:100%;text-align:center;">Módulo Documentos</a>
        <a class="btn" href="/eventos" style="width:100%;text-align:center;">Módulo Eventos</a>
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

        <?php if (!empty($kbSources)): ?>
            <div class="alert" style="background:#1d3a5a;">
                <strong>Fuentes KB usadas:</strong>
                <ul>
                    <?php foreach ($kbSources as $src): ?>
                        <li><?= e((string) $src) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
            <?php if ($error = flash_get('error')): ?>
                <div class="alert"><?= e($error) ?></div>
            <?php endif; ?>
            <?php if ($success = flash_get('success')): ?>
                <div class="alert" style="background:#1f4d30;"><?= e($success) ?></div>
            <?php endif; ?>
            <?php foreach ($messages as $message): ?>
                <article class="msg <?= e($message['sender']) ?>">
                    <strong><?= $message['sender'] === 'assistant' ? 'Asistente IA' : 'Abogado' ?></strong>
                    <div class="msg-md"><?= safe_markdown((string) $message['content']) ?></div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($activeConversation): ?>
        <div class="chat-tools">
            <button type="button" class="btn" data-open-modal="event-modal">+ Crear evento desde chat</button>
        </div>
        <form method="post" action="/chat/message" class="composer">
            <?= csrf_field() ?>
            <input type="hidden" name="conversation_id" value="<?= (int) $activeConversation['id'] ?>">
            <textarea name="content" rows="3" placeholder="Escribe tu consulta legal..." required></textarea>
            <label style="display:flex;gap:8px;align-items:center;">
                <input type="checkbox" name="stream_mode" value="1"> Streaming opcional (OpenRouter)
            </label>
            <button type="submit">Enviar</button>
        </form>

        <div id="event-modal" class="modal-backdrop" hidden>
            <div class="modal-card">
                <h3>Crear evento desde conversación</h3>
                <form method="post" action="/chat/event/create" class="form-grid">
                    <?= csrf_field() ?>
                    <input type="hidden" name="conversation_id" value="<?= (int) $activeConversation['id'] ?>">
                    <label>Título</label>
                    <input type="text" name="title" maxlength="180" value="Seguimiento legal" required>

                    <label>Inicio</label>
                    <input type="datetime-local" name="starts_at" required>

                    <label>Fin</label>
                    <input type="datetime-local" name="ends_at" required>

                    <label>Ubicación (opcional)</label>
                    <input type="text" name="location" maxlength="180" placeholder="Despacho / Zoom / Juzgado">

                    <label>Descripción (opcional)</label>
                    <textarea name="description" rows="3" placeholder="Notas del evento legal"></textarea>

                    <label style="display:flex;gap:8px;align-items:center;">
                        <input type="checkbox" name="download_ics" value="1"> Descargar .ics al crear
                    </label>

                    <div class="modal-actions">
                        <button type="button" class="btn-link" data-close-modal="event-modal">Cancelar</button>
                        <button type="submit">Guardar evento</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </section>
</div>
