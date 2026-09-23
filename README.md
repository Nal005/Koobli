# Koobli

Koobli é uma plataforma web para gerir leituras pessoais, inspirada no Skoob. Cada utilizador pode pesquisar livros, organizá-los em estantes, marcar favoritos, avaliar e comentar.

> **Estado:** em desenvolvimento inicial. As funcionalidades base já existem, mas a interface e várias secções ainda estão a ser construídas.

## Funcionalidades

**Disponíveis**

- **Contas de utilizador:** registo e login, com passwords guardadas em hash (`password_hash`).
- **Pesquisa de livros** através da [Google Books API](https://developers.google.com/books).
- **Carrossel de destaques** na página inicial.
- **Biblioteca pessoal** com quatro estantes: *Quero Ler*, *Lendo*, *Lido* e *Abandonado*. As datas de início e fim de leitura são registadas automaticamente.
- **Favoritos.**
- **Avaliações** de 1 a 5 estrelas, com texto opcional.
- **Comentários** na página de cada livro.

**Planeadas**

- **Salas de leitura coletiva:** espaços interativos onde vários utilizadores leem e discutem o mesmo livro em conjunto.
- **Salas de foco:** sessões de leitura com temporizador, sozinho ou acompanhado, para manter a concentração.
- Perfil de utilizador (foto e biografia).
- Notificações e mensagens entre utilizadores.
- Categorias de livros.

## Tecnologias

- **Backend:** PHP 8 (sem framework)
- **Base de dados:** MySQL / MariaDB, acedida com `mysqli` e prepared statements
- **Frontend:** HTML, CSS e JavaScript simples, com ícones [Flaticon UIcons](https://www.flaticon.com/uicons)
- **API externa:** Google Books API

## Estrutura do projeto

```
.
├── index.php                 # Página inicial: pesquisa e carrossel
├── paginas/                  # Páginas que mostram HTML
│   ├── login.php / registo.php / logout.php
│   ├── biblioteca.php        # Estantes do utilizador
│   ├── favoritos.php         # Lista de favoritos
│   ├── livro.php             # Página de detalhe do livro
│   └── ver_livro.php         # Guarda o livro vindo da API e redireciona para livro.php
├── acoes/                    # Recebem formulários (POST) e redirecionam
│   ├── adicionar_biblioteca.php
│   ├── atualizar_estado.php
│   ├── favoritar.php
│   ├── avaliar.php
│   └── comentar.php
├── includes/
│   ├── config.php            # Ligação à base de dados
│   ├── apikey.php            # Chave da Google Books API (não versionado)
│   ├── livros.php            # Funções auxiliares de livros
│   ├── favoritos_helper.php
│   ├── header.php            # Usa $raiz = "../" nas páginas dentro de subpastas
│   └── footer.php
├── css/style.css
├── imgs/
└── bookhub_db.sql            # Esquema da base de dados
```

## Base de dados

O ficheiro `bookhub_db.sql` cria a base de dados `bookhub_db` com as tabelas seguintes:

| Tabela            | Descrição                                                             |
|-------------------|-----------------------------------------------------------------------|
| `utilizadores`    | Contas de utilizador                                                  |
| `livros`          | Livros guardados localmente, ligados à Google Books API por `api_id`  |
| `biblioteca`      | Relação utilizador ↔ livro, com o estado de leitura e datas           |
| `favoritos`       | Livros favoritos de cada utilizador                                   |
| `avaliacoes`      | Nota (1–5) e texto; uma avaliação por utilizador e livro              |
| `comentarios`     | Comentários nas páginas dos livros                                    |
| `categorias`      | Categorias de livros                                                  |
| `livro_categoria` | Relação livro ↔ categoria                                             |

Um livro só é guardado na tabela `livros` quando algum utilizador interage com ele (abre a página, adiciona à biblioteca, etc.). Até lá, os dados vêm diretamente da API.

## Como correr localmente

### Requisitos

- PHP 8.x
- MySQL ou MariaDB
- Uma chave da [Google Books API](https://console.cloud.google.com/apis/library/books.googleapis.com)

A forma mais simples de ter tudo isto é instalar o [XAMPP](https://www.apachefriends.org/), que já traz Apache, PHP, MariaDB e phpMyAdmin.

### Passos

1. **Clonar o repositório** para a pasta do servidor web (no XAMPP, `htdocs/`):
   ```bash
   git clone https://github.com/Nal005/Koobli.git
   ```

2. **Criar a base de dados:** no phpMyAdmin, importa o ficheiro `bookhub_db.sql`. Em alternativa, pela linha de comandos:
   ```bash
   mysql -u root -p < bookhub_db.sql
   ```

3. **Configurar a ligação à base de dados:** se necessário, ajusta as credenciais em `includes/config.php`.

4. **Configurar a chave da API:** cria o ficheiro `includes/apikey.php` com este conteúdo:
   ```php
   <?php
   define('GOOGLE_BOOKS_API_KEY', 'a-tua-chave-aqui');
   ```
   Este ficheiro está no `.gitignore` e nunca deve ser enviado para o repositório.

5. **Abrir no browser:** `http://localhost/Koobli/`, cria uma conta e começa a usar.

## Autor

Desenvolvido por [Nal005](https://github.com/Nal005) como Projeto Final.
