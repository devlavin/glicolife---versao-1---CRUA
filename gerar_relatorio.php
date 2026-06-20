<?php
/**
 * gerar_relatorio.php
 * Gera o PDF do relatório de glicose.
 * Dependência: mPDF  →  composer require mpdf/mpdf
 */

session_start();
date_default_timezone_set('America/Sao_Paulo');
require 'conexao.php';
require 'helpers_glicose_status.php';
require __DIR__ . '/vendor/autoload.php';

$uid = $_SESSION['usuario_id'];
$periodo = intval($_GET['dias'] ?? 7);
if (!in_array($periodo, [7, 14, 30, 90]))
    $periodo = 7;

// ── Dados do usuário
$uStmt = $conn->prepare("SELECT nome, email, tipo_diabetes FROM usuarios WHERE id = ?");
$uStmt->bind_param('i', $uid);
$uStmt->execute();
$usuario = $uStmt->get_result()->fetch_assoc();

$nomePaciente = $usuario['nome'] ?? 'Paciente';
$tipoDiab = $usuario['tipo_diabetes'] ?? 'tipo 1';
$limites = getLimites($tipoDiab); // usado apenas na legenda do PDF

// ── Glicose
$stmt = $conn->prepare(
    "SELECT valor, momento, registrado_em FROM glicose
     WHERE usuario_id = ? AND registrado_em >= DATE_SUB(NOW(), INTERVAL ? DAY)
     ORDER BY registrado_em ASC"
);
$stmt->bind_param('ii', $uid, $periodo);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Alimentação
$aStmt = $conn->prepare(
    "SELECT tipo, alimento, horario, criado_em FROM alimentacao
     WHERE usuario_id = ? AND criado_em >= DATE_SUB(NOW(), INTERVAL ? DAY)
     ORDER BY criado_em ASC"
);
$aStmt->bind_param('ii', $uid, $periodo);
$aStmt->execute();
$alimentacao = $aStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Medicamentos
$mStmt = $conn->prepare(
    "SELECT nome_remedio, dosagem, via_administracao, horario, frequencia
     FROM medicamentos WHERE usuario_id = ?"
);
$mStmt->bind_param('i', $uid);
$mStmt->execute();
$medicamentos = $mStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total = count($rows);
$media = $total ? round(array_sum(array_column($rows, 'valor')) / $total) : 0;
$maxVal = $total ? max(array_column($rows, 'valor')) : 0;
$minVal = $total ? min(array_column($rows, 'valor')) : 0;

$normais = count(array_filter($rows, function ($r) use ($tipoDiab) {
    $l = getLimitesPorMomento($tipoDiab, $r['momento']);
    return $r['valor'] >= $l['baixo'] && $r['valor'] <= $l['alto'];
}));
$altos = count(array_filter($rows, function ($r) use ($tipoDiab) {
    $l = getLimitesPorMomento($tipoDiab, $r['momento']);
    return $r['valor'] > $l['alto'];
}));
$baixos = count(array_filter($rows, function ($r) use ($tipoDiab) {
    $l = getLimitesPorMomento($tipoDiab, $r['momento']);
    return $r['valor'] < $l['baixo'];
}));

$pctNorm = $total ? round($normais / $total * 100) : 0;
$pctRest = 100 - $pctNorm;

$periodoLabel = [7 => '7 dias', 14 => '14 dias', 30 => '1 mês', 90 => '3 meses'][$periodo];
$dataGeracao = date('d/m/Y \à\s H:i');

$tipoRefeicaoLabel = [
    'cafe' => 'Café da manhã',
    'almoco' => 'Almoço',
    'tarde' => 'Café da tarde',
    'jantar' => 'Jantar',
];

// ── Montar HTML do PDF
ob_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #1a2e25;
            background: #fff;
        }

        /* CABEÇALHO */
        .header {
            background: #0d2218;
            padding: 20px 28px 16px;
        }

        .header-logo {
            font-size: 24px;
            font-weight: bold;
            color: #fff;
        }

        .header-logo span {
            color: #22c896;
        }

        .header-sub {
            font-size: 10px;
            color: #7ab99e;
            margin-top: 3px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* FAIXA DO PACIENTE */
        .patient-bar {
            background: #f0faf5;
            border-bottom: 2px solid #1a9e7a;
            padding: 0;
        }

        .patient-bar table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .patient-bar td {
            padding: 10px 28px;
            color: #5a7a6d;
            border-bottom: none;
            background: none;
        }

        .patient-bar td strong {
            color: #0d2218;
            font-size: 12px;
        }

        .patient-bar .lbl {
            font-size: 9px;
            color: #92b3a5;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 2px;
        }

        .patient-bar .divider {
            border-left: 1px solid #d6f5ea;
        }

        /* AVISO */
        .aviso {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 9px 28px;
            font-size: 10px;
            color: #78350f;
        }

        /* SEÇÕES */
        .section {
            margin: 18px 28px 0;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #fff;
            background: #1a9e7a;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 5px 10px;
            margin-bottom: 12px;
        }

        /* STATS */
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 14px;
        }

        .stats-table td {
            text-align: center;
            border: 1px solid #d6f5ea;
            background: #f8fdfb;
            padding: 10px 6px;
        }

        .stat-val {
            font-size: 20px;
            font-weight: bold;
            color: #1a9e7a;
            line-height: 1.1;
        }

        .stat-lbl {
            font-size: 9px;
            color: #92b3a5;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        /* CLASSIF */
        .classif-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 14px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
        }

        /* BARRA PROGRESSO */
        .prog-wrap {
            margin-bottom: 16px;
        }

        .prog-labels {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: 10px;
            color: #5a7a6d;
        }

        .prog-labels td {
            padding: 0;
            border: none;
            background: none;
        }

        .prog-bar {
            width: 100%;
            border-collapse: collapse;
            height: 8px;
        }

        .prog-bar td {
            padding: 0;
            height: 8px;
            border: none;
        }

        /* TABELA DE DADOS */
        .data {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .data th {
            background: #f0faf5;
            color: #1a9e7a;
            text-transform: uppercase;
            font-size: 9.5px;
            letter-spacing: 0.5px;
            padding: 7px 10px;
            text-align: left;
            border-bottom: 1.5px solid #d6f5ea;
        }

        .data td {
            padding: 7px 10px;
            border-bottom: 1px solid #f0f8f4;
            color: #1a2e25;
        }

        .data tr:nth-child(even) td {
            background: #fafffe;
        }

        /* BADGE */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
        }

        /* RODAPÉ */
        .assinatura {
            margin: 16px 28px 0;
            border-top: 1px dashed #b2d8c8;
            padding-top: 10px;
            font-size: 10px;
            color: #5a7a6d;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            border-top: 1px solid #d6f5ea;
        }

        .footer-table td {
            padding: 8px 28px;
            font-size: 9px;
            color: #92b3a5;
            border: none;
            background: none;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="header-logo">Glico<span>Life</span></div>
        <div class="header-sub">Relatório clínico de monitoramento glicêmico</div>
    </div>

    <div class="patient-bar">
        <table>
            <tr>
                <td>
                    <span class="lbl">Paciente</span>
                    <strong><?= htmlspecialchars($nomePaciente) ?></strong>
                </td>
                <td class="divider">
                    <span class="lbl">Tipo de diabetes</span>
                    <strong><?= htmlspecialchars($tipoDiab) ?></strong>
                </td>
                <td class="divider">
                    <span class="lbl">Período analisado</span>
                    <strong><?= $periodoLabel ?></strong>
                </td>
                <td class="divider">
                    <span class="lbl">Gerado em</span>
                    <strong><?= $dataGeracao ?></strong>
                </td>
            </tr>
        </table>
    </div>

    <div class="aviso">
        ⚠ Este relatório é gerado automaticamente pela plataforma GlicoLife e tem caráter exclusivamente
        informativo. Não substitui avaliação, diagnóstico ou prescrição médica.
    </div>

    <!-- RESUMO -->
    <div class="section">
        <div class="section-title">Resumo do período</div>
        <table class="stats-table">
            <tr>
                <td>
                    <div class="stat-val"><?= $media ?: '—' ?></div>
                    <div class="stat-lbl">Média mg/dL</div>
                </td>
                <td>
                    <div class="stat-val" style="color:#ef4444"><?= $maxVal ?: '—' ?></div>
                    <div class="stat-lbl">Máxima</div>
                </td>
                <td>
                    <div class="stat-val" style="color:#f59e0b"><?= $minVal ?: '—' ?></div>
                    <div class="stat-lbl">Mínima</div>
                </td>
                <td>
                    <div class="stat-val" style="color:#10b981"><?= $pctNorm ?>%</div>
                    <div class="stat-lbl">No alvo</div>
                </td>
                <td>
                    <div class="stat-val"><?= $total ?></div>
                    <div class="stat-lbl">Medições</div>
                </td>
            </tr>
        </table>

        <!-- Classificação com limites dinâmicos por momento -->
        <table class="classif-table">
            <tr>
                <td style="background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;">
                    ✓ Normal (<?= $limites['baixo'] ?>–<?= $limites['alto'] ?>)<br>
                    <span style="font-size:16px"><?= $normais ?></span>
                    <span style="font-size:10px;font-weight:normal"> medições</span>
                </td>
                <td style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;">
                    ↑ Hiperglicemia (&gt;<?= $limites['alto'] ?>)<br>
                    <span style="font-size:16px"><?= $altos ?></span>
                    <span style="font-size:10px;font-weight:normal"> medições</span>
                </td>
                <td style="background:#fef3c7;color:#92400e;border:1px solid #fcd34d;">
                    ↓ Hipoglicemia (&lt;<?= $limites['baixo'] ?>)<br>
                    <span style="font-size:16px"><?= $baixos ?></span>
                    <span style="font-size:10px;font-weight:normal"> medições</span>
                </td>
            </tr>
        </table>

        <div class="prog-wrap">
            <table class="prog-labels">
                <tr>
                    <td>Tempo no alvo (<?= $limites['baixo'] ?>–<?= $limites['alto'] ?> mg/dL)</td>
                    <td style="text-align:right">
                        <?= $normais ?> de <?= $total ?> medições &nbsp;
                        <strong><?= $pctNorm ?>%</strong>
                    </td>
                </tr>
            </table>
            <table class="prog-bar">
                <tr>
                    <td width="<?= $pctNorm ?>%" style="background:#10b981;"></td>
                    <td width="<?= $pctRest ?>%" style="background:#e8f5f0;"></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- TABELA DE GLICOSE -->
    <?php if (!empty($rows)): ?>
        <div class="section" style="margin-top:18px;">
            <div class="section-title">Registros de glicose</div>
            <table class="data">
                <thead>
                    <tr>
                        <th>Data / Hora</th>
                        <th>Momento</th>
                        <th>Glicose (mg/dL)</th>
                        <th>Classificação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($rows) as $row):
                        [$stTxt, $stColor, $stBg] = statusPdf((int) $row['valor'], $tipoDiab, $row['momento']);
                        ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($row['registrado_em'])) ?></td>
                            <td><?= htmlspecialchars($row['momento']) ?></td>
                            <td><strong><?= $row['valor'] ?></strong></td>
                            <td>
                                <span class="badge" style="color:<?= $stColor ?>;background:<?= $stBg ?>">
                                    <?= $stTxt ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- TABELA DE ALIMENTAÇÃO -->
    <?php if (!empty($alimentacao)): ?>
        <div class="section" style="margin-top:18px;">
            <div class="section-title">Registros de alimentação</div>
            <table class="data">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Refeição</th>
                        <th>Alimentos</th>
                        <th>Horário</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alimentacao as $r): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($r['criado_em'])) ?></td>
                            <td><?= $tipoRefeicaoLabel[$r['tipo']] ?? htmlspecialchars($r['tipo']) ?></td>
                            <td><?= htmlspecialchars($r['alimento']) ?></td>
                            <td><?= substr($r['horario'], 0, 5) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- TABELA DE MEDICAMENTOS -->
    <?php if (!empty($medicamentos)): ?>
        <div class="section" style="margin-top:18px;">
            <div class="section-title">Medicamentos em uso</div>
            <table class="data">
                <thead>
                    <tr>
                        <th>Medicamento</th>
                        <th>Dosagem</th>
                        <th>Via</th>
                        <th>Horário</th>
                        <th>Frequência</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medicamentos as $med): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($med['nome_remedio']) ?></strong></td>
                            <td><?= htmlspecialchars($med['dosagem']) ?></td>
                            <td><?= htmlspecialchars($med['via_administracao']) ?></td>
                            <td><?= substr($med['horario'], 0, 5) ?></td>
                            <td><?= htmlspecialchars($med['frequencia']) ?> ao dia</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="assinatura">
        Relatório solicitado e autorizado por: <strong><?= htmlspecialchars($nomePaciente) ?></strong>
        &nbsp;·&nbsp; <?= $dataGeracao ?>
    </div>

    <table class="footer-table">
        <tr>
            <td>GlicoLife · Relatório de monitoramento glicêmico · <?= $dataGeracao ?></td>
            <td style="text-align:right">Documento confidencial — uso clínico</td>
        </tr>
    </table>

</body>

</html>
<?php
$html = ob_get_clean();

$mpdf = new \Mpdf\Mpdf([
    'margin_top' => 0,
    'margin_bottom' => 14,
    'margin_left' => 0,
    'margin_right' => 0,
    'format' => 'A4',
]);

$mpdf->SetTitle("GlicoLife – Relatório $periodoLabel – $nomePaciente");
$mpdf->SetAuthor('GlicoLife');
$mpdf->WriteHTML($html);

$nomeArquivo = 'glicolife_relatorio_' . $periodo . 'dias_' . date('Ymd') . '.pdf';

if (isset($_GET['acao']) && $_GET['acao'] === 'string') {
    echo $mpdf->Output('', 'S');
} else {
    $mpdf->Output($nomeArquivo, 'D');
}
?>