<?php
session_start();
require 'conexao.php';
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['tipo_diabetes'])) {
    $t = $conn->prepare("SELECT tipo_diabetes FROM usuarios WHERE id = ?");
    $t->bind_param('i', $_SESSION['usuario_id']);
    $t->execute();
    $_SESSION['tipo_diabetes'] = $t->get_result()->fetch_assoc()['tipo_diabetes'];
}
$tipo = $_SESSION['tipo_diabetes'];
$nome = explode(" ", $_SESSION['usuario_nome'])[0];

$perguntas = [
    'tipo1' => [
        ['name' => 'aplicacao', 'label' => 'Como você aplica insulina?', 'opcoes' => ['Bomba de insulina', 'Aplicação manual (caneta/seringa)']],
        ['name' => 'hipo_noite', 'label' => 'Costuma ter hipoglicemia à noite?', 'opcoes' => ['Sim, com frequência', 'Às vezes', 'Raramente']],
        ['name' => 'monitoramento', 'label' => 'Com que frequência mede a glicose?', 'opcoes' => ['Mais de 4x ao dia', '2 a 4x ao dia', '1x ao dia ou menos']],
    ],
    'tipo2' => [
        ['name' => 'tratamento', 'label' => 'Qual seu tratamento atual?', 'opcoes' => ['Só dieta e exercício', 'Medicamento oral', 'Insulina', 'Medicamento oral + insulina']],
        ['name' => 'atividade', 'label' => 'Pratica atividade física?', 'opcoes' => ['Sim, regularmente', 'Às vezes', 'Não pratico']],
        ['name' => 'alimentacao', 'label' => 'Como está sua alimentação?', 'opcoes' => ['Sigo dieta específica', 'Tento me cuidar', 'Ainda estou adaptando']],
    ],
    'gestacional' => [
        ['name' => 'semanas', 'label' => 'Quantas semanas de gestação?', 'opcoes' => ['Menos de 20 semanas', '20 a 30 semanas', 'Mais de 30 semanas']],
        ['name' => 'acompanhamento', 'label' => 'Tem acompanhamento médico?', 'opcoes' => ['Sim, obstetra e nutricionista', 'Só obstetra', 'Ainda vou marcar']],
        ['name' => 'insulina', 'label' => 'Usa insulina na gestação?', 'opcoes' => ['Sim', 'Não, só dieta', 'Ainda não sei']],
    ],
    'prediabetes' => [
        ['name' => 'alimentacao', 'label' => 'Já mudou a alimentação?', 'opcoes' => ['Sim, já adotei uma dieta', 'Estou tentando', 'Ainda não']],
        ['name' => 'historico', 'label' => 'Tem histórico familiar de diabetes?', 'opcoes' => ['Sim, pai ou mãe', 'Sim, outros familiares', 'Não tenho']],
        ['name' => 'atividade', 'label' => 'Pratica atividade física?', 'opcoes' => ['Sim, regularmente', 'Às vezes', 'Não pratico']],
    ],
];

$qs = $perguntas[$tipo] ?? $perguntas['tipo2'];
$total = count($qs);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlicoLife - Bem-vindo!</title>
    <link rel="stylesheet" href="css/styleonboarding.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1a9e7a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="GlicoLife">
    <link rel="apple-touch-icon" href="/img/icon-192.png">
</head>

<body>

    <div class="logo">
        <span class="glico">Glico</span><span class="life">Life</span>
    </div>

    <div class="card">

        <!-- Barra de progresso -->
        <div class="progress-wrap">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: 0%"></div>
            </div><span class="progress-texto" id="progressTexto">0 de <?= $total ?></span>
        </div>

        <form method="post" action="salvar_onboarding.php" id="formOnboarding">

            <!-- boas-vindas -->
            <div class="step ativo" id="step-0">
                <div class="intro-badge">Configuração inicial</div>
                <h2 class="intro-title">Olá, <span><?= $nome ?></span>!<br>Vamos te conhecer melhor 👋</h2>
                <p class="intro-sub">
                    São só <?= $total ?> perguntinhas rápidas sobre sua rotina com o diabetes.<br>
                    Isso nos ajuda a personalizar sua experiência no GlicoLife.
                </p>
                <div class="nav-buttons">
                    <button type="button" class="btn-proximo" onclick="irPara(1)">Começar →</button>
                </div>
            </div>

            <!-- STEPS DAS PERGUNTAS -->
            <?php foreach ($qs as $i => $q): ?>
                <div class="step" id="step-<?= $i + 1 ?>">
                    <p class="pergunta-num">Pergunta <?= $i + 1 ?> de <?= $total ?></p>
                    <p class="pergunta-label"><?= $q['label'] ?></p>
                    <div class="opcoes">
                        <?php foreach ($q['opcoes'] as $opcao): ?>
                            <label class="opcao">
                                <span class="opcao-radio"></span>
                                <input type="radio" name="<?= $q['name'] ?>" value="<?= htmlspecialchars($opcao) ?>"
                                    style="display:none" required>
                                <?= htmlspecialchars($opcao) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="nav-buttons">
                        <?php if ($i > 0): ?>
                            <button type="button" class="btn-voltar" onclick="irPara(<?= $i ?>)">← Voltar</button>
                        <?php else: ?>
                            <button type="button" class="btn-voltar" onclick="irPara(0)">← Voltar</button>
                        <?php endif; ?>
                        <?php if ($i + 1 < $total): ?>
                            <button type="button" class="btn-proximo" id="btn-<?= $i + 1 ?>"
                                onclick="avancar(<?= $i + 1 ?>, '<?= $q['name'] ?>')" disabled>Próxima →</button>
                        <?php else: ?>
                            <button type="submit" class="btn-proximo" id="btn-<?= $i + 1 ?>" disabled>Concluir ✓</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

        </form>
    </div>

    <script>
        const total = <?= $total ?>;
        let stepAtual = 0;

        function irPara(n) {
            document.getElementById('step-' + stepAtual).classList.remove('ativo');
            document.getElementById('step-' + n).classList.add('ativo');
            stepAtual = n;
            atualizarProgresso();
        }

        function avancar(stepIndex, name) {
            const selecionado = document.querySelector(`input[name="${name}"]:checked`);
            if (!selecionado) return;
            irPara(stepIndex + 1);
        }

        function atualizarProgresso() {
            const pct = stepAtual === 0 ? 0 : Math.round((stepAtual / total) * 100);
            document.getElementById('progressFill').style.width = pct + '%';
            document.getElementById('progressTexto').textContent =
                stepAtual === 0 ? `0 de ${total}` : `${stepAtual} de ${total}`;
        }

        // habilita botão ao selecionar opção
        document.querySelectorAll('.opcoes').forEach((grupo, i) => {
            grupo.querySelectorAll('input[type=radio]').forEach(radio => {
                radio.addEventListener('change', () => {
                    grupo.querySelectorAll('.opcao').forEach(o => o.classList.remove('selecionada'));
                    radio.closest('.opcao').classList.add('selecionada');
                    // habilita botão
                    const btn = document.getElementById('btn-' + (i + 1));
                    if (btn) btn.disabled = false;
                });
            });
        });
    </script>
</body>

</html>