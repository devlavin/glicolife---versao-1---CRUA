# 🩸 GlicoLife

PROJETO ORIGINAL JÁ DISPONIVEL NA WEB: glicolife.me/

> Controle seu diabetes com leveza.

GlicoLife é uma aplicação web voltada para pessoas com diabetes que precisam monitorar sua saúde no dia a dia. Com uma interface acessível e intuitiva, o sistema centraliza o controle de glicose, medicamentos, alimentação e hidratação em um só lugar — e ainda conta com a **Gotinha**, a mascote da plataforma, que acompanha o usuário com mensagens de incentivo.

---

## Funcionalidades

- **📊 Monitoramento de glicose** — registre suas medições e acompanhe seu histórico com gráficos e estatísticas por período
- **💊 Cadastro de medicamentos** — gerencie seus remédios com horário, dosagem e via de administração, com notificações de lembrete
- **🥗 Registro de alimentação** — anote suas refeições do dia e acompanhe seus hábitos alimentares
- **💧 Meta de água** — controle sua hidratação diária com a meta de copos de água
- **🎙️ Acessibilidade por voz** — fale o valor da glicose ou o nome do alimento e o sistema captura automaticamente; a leitura em voz alta retorna as informações para você
- **🔤 Ajuste de tamanho de texto** — botões de aumentar e diminuir a fonte para melhor conforto visual
- **📄 Relatórios exportáveis** — baixe o PDF do seu histórico de 7, 14, 30 ou 90 dias e envie por e-mail para você mesmo ou para seu médico
- **🩺 Envio ao médico** — compartilhe seu relatório diretamente com seu médico pelo e-mail
- **🐾 Gotinha** — a mascote da plataforma que aparece com mensagens de incentivo conforme seus resultados

---

## Tecnologias

- **PHP** — back-end e geração de relatórios PDF
- **MySQL** — banco de dados
- **HTML, CSS e JavaScript** — front-end
- **PHPMailer** — envio de e-mails (relatórios, verificação OTP, recuperação de senha)
- **mPDF** — geração de relatórios em PDF
- **Chart.js** — gráficos de evolução da glicose
- **Web Speech API** — reconhecimento e síntese de voz
- **PWA** — instalável como aplicativo no celular

---

## Como rodar localmente

### Pré-requisitos
- XAMPP (ou qualquer servidor com PHP 8+ e MySQL)
- Composer

### Instalação

```bash
# Clone o repositório
git clone https://github.com/seu-usuario/glicolife.git

# Entre na pasta
cd glicolife

# Instale as dependências
composer install
```

Copie o arquivo de configuração e preencha com seus dados:

```bash
cp config_example.php config.php
```

Edite o `config.php` com suas credenciais de banco de dados e SMTP.

Importe o banco de dados pelo phpMyAdmin usando o arquivo `.sql` da pasta `banco/`.

Acesse `http://localhost/glicolife` no navegador.

---

## Variáveis de ambiente

O arquivo `config.php` **não é versionado**. Copie o `config_example.php`, renomeie para `config.php` e preencha:

```php
define('DB_HOST', 'localhost');
define('DB_BANCO', 'glicolife');
define('DB_USUARIO', 'root');
define('DB_SENHA', '');

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'seu@email.com');
define('SMTP_PASS', 'sua_senha_de_app');
define('SMTP_FROM', 'seu@email.com');
define('SMTP_FROM_NAME', 'GlicoLife');
```

---

## Autor

Desenvolvido por Lavínia Moreira — estudante de ADS e desenvolvedora em formação.

*"Feito com cuidado para quem cuida da saúde."* 🩸
