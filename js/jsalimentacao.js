// jsalimentacao.js — GlicoLife
// Requer jscommon.js carregado antes deste arquivo.

// ── MODAL ────────────────────────────────────────────────────────────────────
function abrirForm() {
    document.getElementById('modalOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    falar('Formulário de registro de refeição aberto.');
}

function fecharForm() {
    document.getElementById('modalOverlay').style.display = 'none';
    document.body.style.overflow = '';
}

function fecharFormOutside(e) {
    if (e.target === document.getElementById('modalOverlay')) fecharForm();
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') fecharForm();
});

// ── MICROFONE — campo específico desta página ─────────────────────────────────
function micAlimento() {
    iniciarMicrofone('alimento', function (fala) {
        const campo = document.getElementById('alimento');
        const atual = campo.value.trim();
        campo.value = atual ? atual + ', ' + fala : fala;
        falar('Adicionado: ' + fala + '. Pode falar mais ou salvar.');
    });
}