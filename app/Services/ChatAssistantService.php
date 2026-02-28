<?php

declare(strict_types=1);

namespace App\Services;

final class ChatAssistantService
{
    public function buildLegalGuidance(string $question): string
    {
        return "Enfoque legal peruano (referencial):\n\n"
            . "1) Identifique hechos, partes y documentos.\n"
            . "2) Defina la vía procedimental aplicable (civil/laboral/penal/administrativa).\n"
            . "3) Revise plazos, competencia y carga probatoria.\n"
            . "4) Evalúe riesgo, costos y estrategia de negociación o litigio.\n\n"
            . "Consulta: \"{$question}\"\n\n"
            . "⚠️ Orientación general; no sustituye asesoría profesional.";
    }
}
