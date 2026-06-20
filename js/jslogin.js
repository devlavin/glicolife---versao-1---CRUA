// mostrar/ ocultar senha

function toggleSenha(id, btn) {
  const input = document.getElementById(id);
  const aberto = btn.querySelector(".olho-aberto");
  const fechado = btn.querySelector(".olho-fechado");

  if (input.type === "password") {
    input.type = "text";
    aberto.style.display = "none";
    fechado.style.display = "block";
  } else {
    input.type = "password";
    aberto.style.display = "block";
    fechado.style.display = "none";
  }
}
