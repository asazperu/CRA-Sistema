<main class="center-card">
    <section class="card wide">
        <h2>Módulo Eventos</h2>
        <p>Agenda por usuario con vista de lista y calendario mensual.</p>

        <?php if ($error = flash_get('error')): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success = flash_get('success')): ?>
            <div class="alert" style="background:#1f4d30;"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post" action="/eventos/create" class="form-grid">
            <?= csrf_field() ?>
            <div class="two-cols form-grid">
                <div>
                    <label>Título</label>
                    <input type="text" name="title" maxlength="180" required>
                </div>
                <div>
                    <label>Ubicación (opcional)</label>
                    <input type="text" name="location" maxlength="180">
                </div>
                <div>
                    <label>Inicio</label>
                    <input type="datetime-local" name="starts_at" required>
                </div>
                <div>
                    <label>Fin</label>
                    <input type="datetime-local" name="ends_at" required>
                </div>
            </div>
            <label>Descripción</label>
            <textarea name="description" rows="3" placeholder="Notas del evento"></textarea>
            <label style="display:flex;gap:8px;align-items:center;">
                <input type="checkbox" name="download_ics" value="1"> Descargar .ics al crear
            </label>
            <button type="submit">Crear evento</button>
        </form>

        <div style="display:flex; gap:8px; margin-top:16px; flex-wrap:wrap;">
            <a class="btn" href="/eventos?view=list">Ver lista</a>
            <a class="btn" href="/eventos?view=calendar&month=<?= e($monthStart->format('Y-m')) ?>">Ver calendario</a>
            <a class="btn" href="/chat">Volver al chat</a>
        </div>

        <?php if ($viewMode === 'calendar'): ?>
            <div class="calendar-head">
                <a class="btn" href="/eventos?view=calendar&month=<?= e($monthPrev) ?>">← Mes anterior</a>
                <strong><?= e($monthStart->format('F Y')) ?></strong>
                <a class="btn" href="/eventos?view=calendar&month=<?= e($monthNext) ?>">Mes siguiente →</a>
            </div>

            <?php
                $firstDay = (int) $monthStart->format('N');
                $daysInMonth = (int) $monthStart->format('t');
                $cells = [];
                for ($i = 1; $i < $firstDay; $i++) {
                    $cells[] = null;
                }
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $cells[] = $d;
                }
                while (count($cells) % 7 !== 0) {
                    $cells[] = null;
                }
            ?>

            <table class="calendar-table">
                <thead>
                    <tr>
                        <th>Lun</th><th>Mar</th><th>Mié</th><th>Jue</th><th>Vie</th><th>Sáb</th><th>Dom</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_chunk($cells, 7) as $week): ?>
                        <tr>
                            <?php foreach ($week as $day): ?>
                                <td>
                                    <?php if ($day !== null): ?>
                                        <?php $dateKey = $monthStart->format('Y-m-') . str_pad((string) $day, 2, '0', STR_PAD_LEFT); ?>
                                        <div class="calendar-day"><?= (int) $day ?></div>
                                        <?php foreach ($eventsByDay[$dateKey] ?? [] as $event): ?>
                                            <div class="calendar-event">
                                                <span><?= e((new DateTimeImmutable((string) $event['starts_at']))->format('H:i')) ?></span>
                                                <strong><?= e((string) $event['title']) ?></strong>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="margin-top:16px; overflow:auto;">
                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>ID</th><th>Título</th><th>Inicio</th><th>Fin</th><th>Ubicación</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?= (int) $event['id'] ?></td>
                                <td><?= e((string) $event['title']) ?></td>
                                <td><?= e((string) $event['starts_at']) ?></td>
                                <td><?= e((string) $event['ends_at']) ?></td>
                                <td><?= e((string) ($event['location'] ?? '')) ?></td>
                                <td>
                                    <a class="btn" href="/eventos/ics?id=<?= (int) $event['id'] ?>">Exportar .ics</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
