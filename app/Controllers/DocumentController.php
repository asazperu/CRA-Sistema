<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\AuditLog;
use App\Models\Document;

final class DocumentController extends Controller
{
    private const MAX_SIZE = 10485760; // 10MB
    private const ALLOWED_MIME = [
        'application/pdf' => 'pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];

    public function index(): void
    {
        $user = Auth::user();
        $documents = (new Document())->allByUser((int) $user['id']);

        view('document/index', [
            'title' => 'Documentos',
            'user' => $user,
            'documents' => $documents,
        ]);
    }

    public function upload(): void
    {
        verify_csrf();
        $user = Auth::user();

        if (!isset($_FILES['document']) || !is_array($_FILES['document'])) {
            flash('error', 'Debe seleccionar un archivo.');
            redirect('/documentos');
        }

        $file = $_FILES['document'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            flash('error', 'Error al subir archivo.');
            redirect('/documentos');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        $originalName = sanitize_input((string) ($file['name'] ?? 'documento'));
        $size = (int) ($file['size'] ?? 0);

        if ($size <= 0 || $size > self::MAX_SIZE || !is_uploaded_file($tmpName)) {
            flash('error', 'Archivo inválido o excede 10MB.');
            redirect('/documentos');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmpName);
        if (!array_key_exists($mime, self::ALLOWED_MIME)) {
            flash('error', 'Formato no permitido. Solo .pdf y .docx');
            redirect('/documentos');
        }

        $ext = self::ALLOWED_MIME[$mime];
        $uuid = $this->uuidV4();
        $storedName = $uuid . '.' . $ext;

        $baseDir = base_path('storage/documentos/' . (int) $user['id']);
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        $destination = $baseDir . '/' . $storedName;
        if (!move_uploaded_file($tmpName, $destination)) {
            flash('error', 'No se pudo guardar el archivo.');
            redirect('/documentos');
        }

        chmod($destination, 0640);

        $docId = (new Document())->create([
            'user_id' => (int) $user['id'],
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'mime_type' => $mime,
            'extension' => $ext,
            'size_bytes' => $size,
            'checksum_sha256' => hash_file('sha256', $destination) ?: null,
            'storage_path' => 'storage/documentos/' . (int) $user['id'] . '/' . $storedName,
            'processing_status' => 'pending',
        ]);

        $this->audit('document_upload', 'documents', $docId, ['name' => $originalName, 'mime' => $mime]);
        flash('success', 'Documento subido correctamente.');
        redirect('/documentos');
    }

    public function download(): void
    {
        $user = Auth::user();
        $id = (int) ($_GET['id'] ?? 0);

        $document = (new Document())->findByIdForUser($id, (int) $user['id']);
        if (!$document) {
            http_response_code(404);
            exit('Documento no encontrado');
        }

        $filePath = base_path((string) $document['storage_path']);
        if (!is_file($filePath)) {
            http_response_code(404);
            exit('Archivo físico no encontrado');
        }

        $this->audit('document_download', 'documents', (int) $document['id'], []);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $document['mime_type']);
        header('Content-Disposition: attachment; filename="' . basename((string) $document['original_name']) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('X-Content-Type-Options: nosniff');
        readfile($filePath);
        exit;
    }

    public function delete(): void
    {
        verify_csrf();
        $user = Auth::user();
        $id = (int) ($_POST['id'] ?? 0);

        $model = new Document();
        $document = $model->findByIdForUser($id, (int) $user['id']);
        if (!$document) {
            flash('error', 'Documento no encontrado.');
            redirect('/documentos');
        }

        $filePath = base_path((string) $document['storage_path']);
        if (is_file($filePath)) {
            @unlink($filePath);
        }

        $model->delete($id, (int) $user['id']);
        $this->audit('document_delete', 'documents', $id, []);
        flash('success', 'Documento eliminado.');
        redirect('/documentos');
    }

    public function reprocess(): void
    {
        verify_csrf();
        $user = Auth::user();
        $id = (int) ($_POST['id'] ?? 0);

        (new Document())->reprocess($id, (int) $user['id'], 'pending');
        $this->audit('document_reprocess', 'documents', $id, ['status' => 'pending']);
        flash('success', 'Documento marcado para reproceso.');
        redirect('/documentos');
    }

    private function audit(string $action, ?string $entityType, ?int $entityId, array $meta): void
    {
        $user = Auth::user();
        (new AuditLog())->create([
            'user_id' => $user['id'] ?? null,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'metadata' => $meta,
        ]);
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
