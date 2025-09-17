# 🧪 Desafio Técnico – Laravel + Vue

Bem-vindo(a)! 👋  
Este desafio tem como objetivo avaliar suas habilidades práticas em **Laravel (backend)** e **Vue (frontend)**.  

---

## 📌 Contexto
Você foi contratado para implementar funcionalidades de um **ERP de estoque**.  
O sistema precisa permitir **cadastrar produtos**, **registrar compras e vendas**, controlar **estoque** e calcular **lucro**.

---

## 🎯 Objetivos do desafio
- Criar **cadastro de produtos**.  
- Implementar **compra de produtos** (entrada de estoque e atualização do custo médio).  
- Implementar **venda de produtos** (saída de estoque, cálculo de receita e lucro).  
- Criar **telas em Vue** para gerenciar produtos, compras e vendas.  

---

## 🛠️ Backend – Laravel
Implemente os seguintes endpoints:

### Produtos
- **Cadastrar produto**  
  `POST /api/produtos`  
  Campos:  
  - `nome` (obrigatório, mínimo 3 caracteres)  
  - `preco_venda` (valor sugerido de venda, deve ser positivo)  
  - `estoque_inicial = 0`  

- **Listar produtos**  
  `GET /api/produtos`  
  Retornar: id, nome, custo_medio, preco_venda e estoque atual.  

---

### Compras
- **Registrar compra**  
  `POST /api/compras`  
  Payload:
  ```json
  {
    "fornecedor": "Fornecedor X",
    "produtos": [
      { "id": 1, "quantidade": 50, "preco_unitario": 20 },
      { "id": 2, "quantidade": 30, "preco_unitario": 10 }
    ]
  }
  ```

### Regras:

  - Atualizar estoque (entrada).

  - Atualizar custo médio do produto

💰 Vendas

Registrar venda
POST /api/vendas
Payload:
  ```json
  {
    "cliente": "Fulano da Silva",
    "produtos": [
      { "id": 1, "quantidade": 2, "preco_unitario": 50 },
      { "id": 3, "quantidade": 1, "preco_unitario": 100 }
    ]
  }
  ```

Regras:
  
  - Validar estoque suficiente.
  
  - Baixar estoque (saída).
  
  - Calcular lucro da venda


  - Retornar no JSON o total da venda e o lucro calculado.

  - Cancelar venda (opcional)

  - Deve reverter o estoque.

💻 Frontend – Vue

Implemente as seguintes telas:

  - Cadastro de produto

  - Formulário com nome e preço de venda sugerido.

  - Mostrar lista de produtos com custo médio, preço e estoque.

  - Cadastro de compra

  - Formulário para adicionar produtos, quantidades e preço unitário.

  - Atualizar estoque e custo médio.

  - Cadastro de venda

  - Formulário para selecionar produtos e quantidades.

  - Mostrar total da venda e lucro estimado.

  - Exibir mensagens de sucesso ou erro (ex: “Estoque insuficiente”).

⚡ Diferencial: Tela para listar todas as vendas e compras.

# Como executar o projeto
Este projeto utiliza **Laravel** no backend, **Vue 3** no frontend (com Vite) e **MySQL** como banco de dados via **Docker Compose**.  

## 🚀 Pré-requisitos

Antes de começar, certifique-se de ter instalado:

- [PHP 8.2+](https://www.php.net/downloads)  
- [Composer](https://getcomposer.org/)  
- [Node.js 18+](https://nodejs.org/en/)  
- [NPM](https://www.npmjs.com/) ou [Yarn](https://yarnpkg.com/)  
- [Docker](https://www.docker.com/) e [Docker Compose](https://docs.docker.com/compose/)  

## ⚙️ Instalação
1. **Instale as dependências do backend (Laravel)**
```bash
composer install
```
2. **Instale as dependências do frontend (Vue)**
```bash
npm install
```
3. **Configure o arquivo `.env`**
```bash
Crie uma cópia do arquivo `.env.example` e renomeie para `.env`.  
```

## 🐳 Subindo o Banco de Dados com Docker
1. **Inicie os containers**
```bash
docker-compose up -d
```
2. **Aplique as migrações do banco de dados**
```bash
php artisan migrate
```

## ▶️ Executando o Projeto
1. **Inicie o servidor backend (Laravel)**
```bash
php artisan serve
```
2. **Inicie o servidor frontend (Vue)**
```bash
npm run dev
```

## 🔗 Acesso
- **Backend**: `http://localhost:8000`
- **Frontend**: `http://localhost:3000`
- **phpMyAdmin**: `http://localhost:8080`
- **Banco de Dados / Diagrama**: `https://dbdiagram.io/d/ERP-estoque-68c2385161a46d388e8353f0`







