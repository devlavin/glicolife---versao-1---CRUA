// ── MODAIS
function abrirForm() {
  document.getElementById("modalOverlay").style.display = "flex";
  document.body.style.overflow = "hidden";
  falar("Formulário de cadastro de medicamento aberto.");
}

function fecharForm() {
  document.getElementById("modalOverlay").style.display = "none";
  document.body.style.overflow = "";
}

function fecharFormOutside(e) {
  if (e.target === document.getElementById("modalOverlay")) fecharForm();
}

function abrirEdicao(med) {
  document.getElementById("edit-id").value = med.id;
  document.getElementById("edit-nome").value = med.nome_remedio;
  document.getElementById("edit-dosagem").value = med.dosagem;
  document.getElementById("edit-via").value = med.via_administracao;
  document.getElementById("edit-horario").value = med.horario;
  document.getElementById("edit-frequencia").value = med.frequencia;
  document.getElementById("modalEditar").style.display = "flex";
  document.body.style.overflow = "hidden";
  falar("Editando medicamento: " + med.nome_remedio);
}

function fecharEdicao() {
  document.getElementById("modalEditar").style.display = "none";
  document.body.style.overflow = "";
}

function confirmarExclusao(id) {
  document.getElementById("btnConfirmarExclusao").href =
    "excluir_medicamento.php?id=" + id;
  document.getElementById("modalConfirmarExclusao").style.display = "flex";
  document.body.style.overflow = "hidden";
  falar("Confirme a exclusão do medicamento.");
}

function fecharConfirmarExclusao() {
  document.getElementById("modalConfirmarExclusao").style.display = "none";
  document.body.style.overflow = "";
}

// ── MICROFONE — campos específicos desta página
function micNome() {
  iniciarMicrofone("nome", function (fala) {
    document.getElementById("nome_remedio").value = fala;
    falar("Nome capturado: " + fala + ". Correto?");
  });
}

function micDosagem() {
  iniciarMicrofone("dosagem", function (fala) {
    document.getElementById("dosagem").value = fala;
    falar("Dosagem capturada: " + fala + ". Correto?");
  });
}
