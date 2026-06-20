// ── MODAL EDIÇÃO DE DADOS
function abrirEdicao() {
  document.getElementById("modalEdicao").classList.add("open");
  document.body.style.overflow = "hidden";
}

function fecharEdicao() {
  document.getElementById("modalEdicao").classList.remove("open");
  document.body.style.overflow = "";
}

function fecharSeForaDoModal(e) {
  if (e.target === document.getElementById("modalEdicao")) fecharEdicao();
}

// ── MODAL FOTO
function abrirEdicaoFoto() {
  document.getElementById("modalFoto").classList.add("open");
  document.body.style.overflow = "hidden";
}

function fecharEdicaoFoto() {
  document.getElementById("modalFoto").classList.remove("open");
  document.body.style.overflow = "";
}

document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("inputFoto").addEventListener("change", function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (e) => {
      document.getElementById("previewFoto").src = e.target.result;
      document.getElementById("previewFoto").style.display = "block";
      document.getElementById("previewIniciais").style.display = "none";
    };
    reader.readAsDataURL(file);
  });
});

// ── MODAL EXCLUIR CONTA
function abrirExcluirConta() {
  document.getElementById("modalExcluir").classList.add("open");
  document.body.style.overflow = "hidden";
}

function fecharExcluirConta() {
  document.getElementById("modalExcluir").classList.remove("open");
  document.body.style.overflow = "";
}

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    fecharEdicao();
    fecharEdicaoFoto();
    fecharExcluirConta();
  }
});
