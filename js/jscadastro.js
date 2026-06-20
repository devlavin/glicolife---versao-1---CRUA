// Máscara — celular 

document.getElementById('celular').addEventListener('input', function () {
  let v = this.value.replace(/\D/g, '').slice(0, 11);
  v = v.replace(/^(\d{2})(\d)/, '($1) $2');
  v = v.replace(/(\d{5})(\d{1,4})$/, '$1-$2');
  this.value = v;
});


// Select — estado preenchido

document.querySelectorAll('select').forEach(sel => {
  sel.addEventListener('change', () => sel.classList.toggle('filled', sel.value !== ''));
});


// Forçar senha 

const senhaInput    = document.getElementById('senha');
const bars          = ['s1', 's2', 's3', 's4'].map(id => document.getElementById(id));
const strengthText  = document.getElementById('strength-text');
const colors        = ['#ef4444', '#f97316', '#eab308', '#1a9e7a'];
const labels        = ['Muito fraca', 'Fraca', 'Razoável', 'Forte'];

senhaInput.addEventListener('input', function () {
  const v = this.value;
  let score = 0;

  if (v.length >= 8)          score++;
  if (/[A-Z]/.test(v))        score++;
  if (/[0-9]/.test(v))        score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;

  bars.forEach((b, i) => b.style.background = i < score ? colors[score - 1] : '#e3f2ec');

  strengthText.textContent = v.length ? (labels[score - 1] || '') : '';
  strengthText.style.color = v.length ? colors[score - 1] : '#8aab9e';
});


// Validação

document.getElementById('form-cadastro').addEventListener('submit', function (e) {
  const erro = document.getElementById('erro-inline');
  erro.style.display = 'none';

  function show(msg) {
    erro.textContent = msg;
    erro.style.display = 'block';
  }

  const cel = document.getElementById('celular').value.replace(/\D/g, '');
  if (cel.length !== 11) { show('Digite um celular válido com DDD (11 dígitos).'); e.preventDefault(); return; }

  const senha = senhaInput.value;
  if (senha.length < 8)       { show('A senha precisa ter pelo menos 8 caracteres.'); e.preventDefault(); return; }
  if (!/[A-Z]/.test(senha))   { show('Inclua pelo menos uma letra maiúscula.');       e.preventDefault(); return; }
  if (!/[0-9]/.test(senha))   { show('Inclua pelo menos um número.');                 e.preventDefault(); return; }

  const nasc = document.getElementById('nascimento').value;
  if (nasc) {
    const ano = parseInt(nasc.split('-')[0]);
    if (ano < 1900 || ano > new Date().getFullYear()) {
      show('Data de nascimento inválida.');
      e.preventDefault();
      return;
    }
  }
});


// mostrar/ocultar senha 

function toggleSenha(id, btn) {
  const input   = document.getElementById(id);
  const aberto  = btn.querySelector('.olho-aberto');
  const fechado = btn.querySelector('.olho-fechado');

  if (input.type === 'password') {
    input.type = 'text';
    aberto.style.display  = 'none';
    fechado.style.display = 'block';
  } else {
    input.type = 'password';
    aberto.style.display  = 'block';
    fechado.style.display = 'none';
  }
}