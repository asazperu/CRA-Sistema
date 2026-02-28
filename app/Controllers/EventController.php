<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Event;

final class EventController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();
        $viewMode = (string) ($_GET['view'] ?? 'list');
        $viewMode = in_array($viewMode, ['list', 'calendar'], true) ? $viewMode : 'list';

        $monthParam = (string) ($_GET['month'] ?? date('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            $monthParam = date('Y-m');
        }

        $monthStart = new \DateTimeImmutable($monthParam . '-01 00:00:00');
        $monthEnd = $monthStart->modify('last day of this month')->setTime(23, 59, 59);

        $eventModel = new Event();
        $events = $eventModel->allByUser((int) $user['id']);
        $eventsInMonth = $eventModel->allByUserBetween(
            (int) $user['id'],
            $monthStart->format('Y-m-d H:i:s'),
            $monthEnd->format('Y-m-d H:i:s')
        );

        $eventsByDay = [];
        foreach ($eventsInMonth as $event) {
            $day = substr((string) $event['starts_at'], 0, 10);
            $eventsByDay[$day][] = $event;
        }

        view('events/index', [
            'title' => 'Eventos',
            'user' => $user,
            'events' => $events,
            'viewMode' => $viewMode,
            'monthStart' => $monthStart,
            'monthPrev' => $monthStart->modify('-1 month')->format('Y-m'),
            'monthNext' => $monthStart->modify('+1 month')->format('Y-m'),
            'eventsByDay' => $eventsByDay,
        ]);
    }

    public function create(): void
    {
        verify_csrf();
        $user = Auth::user();

        $title = mb_substr(sanitize_input((string) ($_POST['title'] ?? 'Evento legal')), 0, 180);
        $description = trim((string) ($_POST['description'] ?? ''));
        $location = mb_substr(sanitize_input((string) ($_POST['location'] ?? '')), 0, 180);
        $startsAtInput = (string) ($_POST['starts_at'] ?? '');
        $endsAtInput = (string) ($_POST['ends_at'] ?? '');
        $conversationId = (int) ($_POST['conversation_id'] ?? 0);

        if ($title === '') {
            $title = 'Evento legal';
        }

        $startsAt = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $startsAtInput) ?: null;
        $endsAt = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $endsAtInput) ?: null;

        if (!$startsAt || !$endsAt || $endsAt < $startsAt) {
            flash('error', 'Fechas inválidas.');
            redirect('/eventos');
        }

        $eventId = (new Event())->create([
            'user_id' => (int) $user['id'],
            'conversation_id' => $conversationId > 0 ? $conversationId : null,
            'title' => $title,
            'description' => $description,
            'location' => $location,
            'starts_at' => $startsAt->format('Y-m-d H:i:s'),
            'ends_at' => $endsAt->format('Y-m-d H:i:s'),
        ]);

        flash('success', 'Evento creado correctamente.');

        if (($_POST['download_ics'] ?? '0') === '1') {
            redirect('/eventos/ics?id=' . $eventId);
        }

        $fromChat = (($_POST['from_chat'] ?? '0') === '1');
        if ($fromChat && $conversationId > 0) {
            redirect('/chat?id=' . $conversationId);
        }

        redirect('/eventos');
    }

    public function downloadIcs(): void
    {
        $user = Auth::user();
        $id = (int) ($_GET['id'] ?? 0);
        $event = (new Event())->findByIdForUser($id, (int) $user['id']);

        if (!$event) {
            http_response_code(404);
            exit('Evento no encontrado');
        }

        $ics = $this->buildIcs($event);
        $slug = preg_replace('/[^a-z0-9]+/i', '-', (string) $event['title']) ?: 'evento';
        $fileName = strtolower(trim($slug, '-')) . '.ics';

        header('Content-Type: text/calendar; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('X-Content-Type-Options: nosniff');
        echo $ics;
        exit;
    }

    private function buildIcs(array $event): string
    {
        $uid = 'event-' . (int) $event['id'] . '@cra-sistema.local';
        $dtStamp = gmdate('Ymd\THis\Z');
        $dtStart = (new \DateTimeImmutable((string) $event['starts_at']))->format('Ymd\THis');
        $dtEnd = (new \DateTimeImmutable((string) $event['ends_at']))->format('Ymd\THis');

        $title = $this->escapeIcsText((string) $event['title']);
        $description = $this->escapeIcsText((string) ($event['description'] ?? ''));
        $location = $this->escapeIcsText((string) ($event['location'] ?? ''));

        return "BEGIN:VCALENDAR\r\n"
            . "VERSION:2.0\r\n"
            . "PRODID:-//CRA Sistema//Eventos//ES\r\n"
            . "CALSCALE:GREGORIAN\r\n"
            . "METHOD:PUBLISH\r\n"
            . "BEGIN:VEVENT\r\n"
            . "UID:" . $uid . "\r\n"
            . "DTSTAMP:" . $dtStamp . "\r\n"
            . "DTSTART:" . $dtStart . "\r\n"
            . "DTEND:" . $dtEnd . "\r\n"
            . "SUMMARY:" . $title . "\r\n"
            . "DESCRIPTION:" . $description . "\r\n"
            . "LOCATION:" . $location . "\r\n"
            . "END:VEVENT\r\n"
            . "END:VCALENDAR\r\n";
    }

    private function escapeIcsText(string $value): string
    {
        $escaped = str_replace('\\', '\\\\', $value);
        $escaped = str_replace(';', '\\;', $escaped);
        $escaped = str_replace(',', '\\,', $escaped);
        return str_replace(["\r\n", "\n", "\r"], '\\n', $escaped);
    }
}
