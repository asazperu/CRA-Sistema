<main class="center-card">
    <section class="card wide">
        <h2>Módulo Documentos</h2>
        <p>Sube y gestiona archivos .pdf/.docx con permisos por usuario.</p>

        <?php if ($error = flash_get('error')): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success = flash_get('success')): ?>
            <div class="alert" style="background:#1f4d30;"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post" action="/documentos/upload" enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>
            <label>Archivo (.pdf o .docx, máx 10MB)</label>
            <input type="file" name="document" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
            <button type="submit">Subir documento</button>
        </form>

        <div style="margin-top:16px; overflow:auto;">
            <table style="width:100%; border-collapse: collapse;">
                <thead><tr><th>ID</th><th>Nombre</th><th>MIME</th><th>Tamaño</th><th>Estado</th><th>Aviso</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($documents as $d): ?>
                    <tr>
                        <td><?= (int) $d['id'] ?></td>
                        <td><?= e((string) $d['original_name']) ?></td>
                        <td><?= e((string) $d['mime_type']) ?></td>
                        <td><?= e((string) $d['size_bytes']) ?> bytes</td>
                        <td><?= e((string) ($d['processing_status'] ?? 'pending')) ?></td>
                        <td><?= e((string) ($d['parse_warning'] ?? '')) ?></td>
                        <td>
                            <a class="btn" href="/documentos/download?id=<?= (int) $d['id'] ?>">Descargar</a>
                            <form method="post" action="/documentos/reprocess" style="display:inline-block;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                                <button type="submit">Reprocesar</button>
                            </form>
                            <form method="post" action="/documentos/delete" style="display:inline-block;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                                <button type="submit" class="danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <a class="btn" href="/chat">Volver al chat</a>
    </section>
</main>
