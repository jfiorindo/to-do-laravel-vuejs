# To-Do List - Laravel + Vue.js

Este é um sistema completo de gerenciamento de tarefas com autenticação, painel administrativo e interface moderna usando Vue.js + Inertia.js. Ideal para testes, entrevistas ou como projeto base para sistemas maiores.

---

## ✅ Funcionalidades

### Usuário comum:
- Visualiza suas tarefas
- Marca tarefas como concluídas/não concluídas
- Exporta suas tarefas para CSV
- Altera sua própria senha

### Administrador:
- Acessa o painel `/admin`
- Visualiza tarefas de todos os usuários
- Exporta todas as tarefas
- Gerencia usuários (criar, editar, excluir)
- Visualiza estatísticas de tarefas por usuário

---

## 🧰 Pré-requisitos

- XAMPP (Apache + PHP 8+): https://www.apachefriends.org/pt_br/index.html
- Composer (gerenciador de pacotes PHP): https://getcomposer.org/
- Node.js + NPM (para build do front-end): https://nodejs.org/

---

## 🚀 Instalação do Projeto

### 1. Clone o projeto

```
git clone https://github.com/jfiorindo/to-do-laravel-vuejs
cd seu-repositorio
```

### 2. Instale as dependências PHP

```
composer install
```

### 3. Instale as dependências front-end

```
npm install
```

### 4. Configure o `.env`

Copie o arquivo de exemplo e gere a chave da aplicação:

```
cp .env.example .env
php artisan key:generate
```

---

## 🗄️ Banco de Dados (SQLite)

Este projeto já acompanha o arquivo `database/database.sqlite` **com os usuários de teste criados**. Você **não precisa criar manualmente o banco**.

**Importante:** certifique-se de que o caminho do banco esteja corretamente configurado no arquivo `.env`:

```
DB_CONNECTION=sqlite
```

Não é necessário configurar `DB_HOST`, `DB_USERNAME` nem `DB_PASSWORD`.

---

## 🏗️ Rodando a aplicação

### 1. Inicie o servidor Laravel

```
php artisan serve
```

Acesse: http://127.0.0.1:8000

### 2. Inicie o front-end (em outro terminal)

```
npm run dev
```

---

## 👥 Usuários para Teste

### Administrador
- Email: admin@teste.com
- Senha: admin123

### Usuário Comum
- Email: usuario@teste.com
- Senha: usuario123

---

## 🧪 Testando o Sistema

1. Acesse: http://127.0.0.1:8000/login
2. Faça login com um dos usuários de teste.
3. Navegue pelo sistema:
   - Usuário comum verá apenas suas tarefas e poderá marcá-las como feitas/não feitas.
   - Admin verá o painel de controle e poderá gerenciar usuários e tarefas.

---

## 📝 Observações

- Usuários comuns **não podem editar ou excluir tarefas**, apenas visualizar e marcar como feitas.
- O campo de senha **só é obrigatório ao criar um novo usuário** no painel do admin.
- As tarefas têm filtros automáticos por vencimento e conclusão.

---

## 📂 Estrutura do Projeto

- Laravel Breeze com Vue.js + Inertia.js
- Banco SQLite incluso no repositório (`database/database.sqlite`)
- Painel Admin acessível em `/admin` para usuários com `is_admin = true`

---

## ✅ Pronto!

Se tudo estiver correto, o sistema estará funcionando com dois usuários de teste e todos os recursos disponíveis.
