<?php
session_start();
require 'conexao.php';
require_once __DIR__ . '/config.php';
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── Validar entrada
$uid          = $_SESSION['usuario_id'];
$dias         = intval($_POST['dias'] ?? 7);
$tipo         = $_POST['tipo'] ?? 'proprio';
$emailDestino = trim($_POST['email_destino'] ?? '');
$nomeMedico   = trim($_POST['nome_medico']   ?? '');
$observacao   = trim($_POST['observacao']    ?? '');

if (!in_array($dias, [7, 14, 30, 90]) || !filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
    header('Location: relatorio.php?dias=' . $dias . '&erro=dados_invalidos');
    exit;
}

// ── Dados do remetente (usuário)
$uStmt = $conn->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
$uStmt->bind_param('i', $uid);
$uStmt->execute();
$usuario     = $uStmt->get_result()->fetch_assoc();
$nomeUsuario = $usuario['nome'] ?? 'Usuário';

$periodoLabel = [7 => '7 dias', 14 => '14 dias', 30 => '1 mês', 90 => '3 meses'][$dias];

// ── Gerar PDF
$_GET['dias'] = $dias;
$_GET['acao'] = 'string';

ob_start();
include __DIR__ . '/gerar_relatorio.php';
$pdfContent = ob_get_clean();

// ── Montar e-mail
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress($emailDestino, $tipo === 'medico' ? $nomeMedico : $nomeUsuario);
    $mail->addReplyTo($usuario['email'] ?? SMTP_FROM, $nomeUsuario);

    $nomeArquivo = 'glicolife_relatorio_' . $dias . 'dias_' . date('Ymd') . '.pdf';
    $mail->addStringAttachment($pdfContent, $nomeArquivo, 'base64', 'application/pdf');

    if ($tipo === 'medico') {
        $mail->Subject = "Relatório GlicoLife de {$nomeUsuario} – {$periodoLabel}";
        $mail->Body    = montarEmailMedico($nomeUsuario, $nomeMedico, $periodoLabel, $observacao);
        $mail->AltBody = "Prezado(a) {$nomeMedico},\n\nO paciente {$nomeUsuario} compartilhou um relatório GlicoLife referente aos últimos {$periodoLabel}.\n\n" . ($observacao ? "Observação: {$observacao}\n\n" : '') . "O relatório completo está em anexo (PDF).\n\nAtenciosamente,\nGlicoLife";
    } else {
        $mail->Subject = "Seu relatório GlicoLife – {$periodoLabel}";
        $mail->Body    = montarEmailProprio($nomeUsuario, $periodoLabel);
        $mail->AltBody = "Olá {$nomeUsuario},\n\nSeu relatório GlicoLife dos últimos {$periodoLabel} está em anexo.\n\nAtenciosamente,\nGlicoLife";
    }

    $mail->isHTML(true);
    $mail->send();

    header('Location: relatorio.php?dias=' . $dias . '&enviado=1');
    exit;

} catch (Exception $e) {
    error_log('Erro ao enviar e-mail GlicoLife: ' . $mail->ErrorInfo);
    header('Location: relatorio.php?dias=' . $dias . '&erro=falha_envio');
    exit;
}

// ─── Templates ──────────────────────────────────────────────────────────────

function montarEmailProprio(string $nome, string $periodo): string
{
    return "
    <div style='font-family:Arial,sans-serif;max-width:520px;margin:0 auto;background:#f4f8f6;padding:32px 20px'>
        <div style='background:#0d2218;border-radius:16px 16px 0 0;padding:24px 28px'>
            <span style='font-size:20px;font-weight:bold;color:#fff'>Glico<span style='color:#22c896'>Life</span></span>
        </div>
        <div style='background:#fff;border-radius:0 0 16px 16px;padding:28px;border:1px solid #e0f2eb;border-top:none'>
            <h2 style='color:#0d2218;font-size:18px;margin-bottom:8px'>Seu relatório está pronto 📊</h2>
            <p style='color:#5a7a6d;font-size:14px;line-height:1.6'>
                Olá, <strong>{$nome}</strong>!<br><br>
                O relatório dos seus dados GlicoLife referente aos últimos <strong>{$periodo}</strong> está em anexo neste e-mail.<br><br>
                O documento inclui suas medições de glicose, alimentação e medicamentos no período.
            </p>
            <div style='background:#f0faf5;border-radius:10px;padding:14px 18px;margin:20px 0;border-left:4px solid #1a9e7a;font-size:13px;color:#5a7a6d'>
                ⚠️ Este relatório é apenas informativo. Sempre consulte seu médico para orientações.
            </div>
            <p style='color:#92b3a5;font-size:12px;margin-top:20px'>Atenciosamente,<br><strong>Equipe GlicoLife</strong></p>
        </div>
    </div>";
}

function montarEmailMedico(string $nomePaciente, string $nomeMedico, string $periodo, string $obs): string
{
    $saudacao = $nomeMedico ? "Prezado(a) Dr(a). {$nomeMedico}," : "Prezado(a) médico(a),";
    $obsHtml  = $obs ? "<div style='background:#f0faf5;border-radius:10px;padding:14px 18px;margin:20px 0;border-left:4px solid #1a9e7a;font-size:13px;color:#5a7a6d'><strong>Observação do paciente:</strong><br>{$obs}</div>" : '';
    return "
    <div style='font-family:Arial,sans-serif;max-width:520px;margin:0 auto;background:#f4f8f6;padding:32px 20px'>
        <div style='background:#0d2218;border-radius:16px 16px 0 0;padding:24px 28px'>
            <span style='font-size:20px;font-weight:bold;color:#fff'>Glico<span style='color:#22c896'>Life</span></span>
        </div>
        <div style='background:#fff;border-radius:0 0 16px 16px;padding:28px;border:1px solid #e0f2eb;border-top:none'>
            <h2 style='color:#0d2218;font-size:18px;margin-bottom:8px'>Relatório de paciente 🩺</h2>
            <p style='color:#5a7a6d;font-size:14px;line-height:1.6'>
                {$saudacao}<br><br>
                O(a) paciente <strong>{$nomePaciente}</strong> compartilhou um relatório GlicoLife referente aos últimos <strong>{$periodo}</strong>.<br><br>
                O documento em anexo contém medições de glicose, alimentação e medicamentos do período.
            </p>
            {$obsHtml}
            <p style='color:#92b3a5;font-size:12px;margin-top:20px'>
                Este e-mail foi enviado automaticamente pela plataforma GlicoLife a pedido do paciente.
            </p>
        </div>
    </div>";
}
?>