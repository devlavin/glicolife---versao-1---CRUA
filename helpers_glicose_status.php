<?php
function getLimites(string $tipo): array
{
    return match (strtolower(trim($tipo))) {
        'gestacional' => ['baixo' => 65, 'alto' => 95],
        'pré-diabetes', 'pre-diabetes' => ['baixo' => 70, 'alto' => 100],
        'tipo 2', 'tipo2' => ['baixo' => 70, 'alto' => 180],
        default => ['baixo' => 70, 'alto' => 180], // Tipo 1
    };
}
function getLimitesPorMomento(string $tipo, string $momento): array
{
    $tipo = strtolower(trim($tipo));
    $momento = strtolower(trim($momento));
    $ePos = str_contains($momento, 'pós') || str_contains($momento, 'pos');

    if ($tipo === 'gestacional') {
        return $ePos
            ? ['baixo' => 65, 'alto' => 140]
            : ['baixo' => 65, 'alto' => 95];
    }

    if (in_array($tipo, ['prediabetes'])) {
        return $ePos
            ? ['baixo' => 70, 'alto' => 140]
            : ['baixo' => 70, 'alto' => 100];
    }

    if ($ePos || str_contains($momento, 'deitar')) {
        return ['baixo' => 70, 'alto' => 180];
    }
    return ['baixo' => 70, 'alto' => 130];
}
function statusGlicose(int $valor, string $tipoDiabetes, string $momento = ''): array
{
    $l = $momento !== ''
        ? getLimitesPorMomento($tipoDiabetes, $momento)
        : getLimites($tipoDiabetes);

    if ($valor < $l['baixo'])
        return ['Hipoglicemia', 'badge-amber', '#f59e0b', 'rgba(245,158,11,0.09)'];
    if ($valor > $l['alto'])
        return ['Hiperglicemia', 'badge-rose', '#ef4444', 'rgba(239,68,68,0.08)'];

    return ['Normal', 'badge-green', '#10b981', 'rgba(16,185,129,0.08)'];
}
function statusPdf(int $valor, string $tipoDiabetes, string $momento = ''): array
{
    $l = $momento !== ''
        ? getLimitesPorMomento($tipoDiabetes, $momento)
        : getLimites($tipoDiabetes);

    if ($valor < $l['baixo'])
        return ['Hipoglicemia', '#92400e', '#fef3c7'];
    if ($valor > $l['alto'])
        return ['Hiperglicemia', '#991b1b', '#fee2e2'];
    return ['Normal', '#065f46', '#d1fae5'];
}
?>