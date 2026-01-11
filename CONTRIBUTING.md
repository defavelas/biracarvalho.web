# Guia de Contribuição

Agradecemos seu interesse em contribuir com o **Programa Bira Carvalho**! Este documento descreve o processo para contribuir com o projeto seguindo o fluxo de trabalho [fork e pull request](https://docs.github.com/pt/get-started/exploring-projects-on-github/contributing-to-a-project).

## Índice

- [Tipos de Contribuição](#tipos-de-contribuição)
- [Antes de Começar](#antes-de-começar)
- [Fluxo de Trabalho](#fluxo-de-trabalho)
  - [1. Fork do Repositório](#1-fork-do-repositório)
  - [2. Clone o Repositório](#2-clone-o-repositório)
  - [3. Configure o Ambiente](#3-configure-o-ambiente)
  - [4. Crie uma Branch](#4-crie-uma-branch)
  - [5. Faça suas Alterações](#5-faça-suas-alterações)
  - [6. Commit suas Alterações](#6-commit-suas-alterações)
  - [7. Push para o GitHub](#7-push-para-o-github)
  - [8. Abra um Pull Request](#8-abra-um-pull-request)
- [Padrões de Código](#padrões-de-código)
- [Padrões de Commit](#padrões-de-commit)
- [Revisão de Código](#revisão-de-código)

## Tipos de Contribuição

Aceitamos diversos tipos de contribuições:

| Tipo | Descrição |
|------|-----------|
| **Bug fixes** | Correções de erros e problemas |
| **Features** | Novas funcionalidades |
| **Documentação** | Melhorias na documentação |
| **Testes** | Adição de testes automatizados |
| **Refatoração** | Melhorias no código existente |
| **Acessibilidade** | Melhorias de acessibilidade na interface |

## Antes de Começar

1. Verifique se já existe uma [issue](../../issues) relacionada ao que você deseja contribuir
2. Se não existir, crie uma nova issue descrevendo sua proposta
3. Aguarde feedback dos mantenedores antes de começar a trabalhar em mudanças significativas

## Fluxo de Trabalho

### 1. Fork do Repositório

Clique no botão **Fork** no canto superior direito da página do repositório no GitHub. Isso criará uma cópia do repositório na sua conta.

### 2. Clone o Repositório

Clone o fork para sua máquina local:

```bash
git clone https://github.com/seu-usuario/biracarvalho.web.git
cd biracarvalho.web
```

Adicione o repositório original como remote upstream:

```bash
git remote add upstream https://github.com/observatorio-de-favelas/biracarvalho.web.git
```

### 3. Configure o Ambiente

Siga as instruções do [README.md](README.md#como-executar-o-projeto) para configurar o ambiente de desenvolvimento com Docker e Laravel Sail.

```bash
# Copie o arquivo de ambiente
cp .env.example .env

# Instale as dependências
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# Inicie os containers
./vendor/bin/sail up -d

# Gere a chave da aplicação
./vendor/bin/sail artisan key:generate

# Execute as migrations
./vendor/bin/sail artisan migrate

# Instale dependências do frontend
./vendor/bin/sail npm install
```

### 4. Crie uma Branch

Antes de fazer alterações, crie uma nova branch a partir da branch principal:

```bash
# Atualize sua branch principal
git checkout main
git pull upstream main

# Crie uma nova branch
git checkout -b tipo/descricao-curta
```

**Convenção de nomes para branches:**

- `feature/` - Nova funcionalidade (ex: `feature/filtro-por-bairro`)
- `fix/` - Correção de bug (ex: `fix/erro-login`)
- `docs/` - Documentação (ex: `docs/guia-instalacao`)
- `refactor/` - Refatoração (ex: `refactor/location-service`)
- `test/` - Testes (ex: `test/location-model`)

### 5. Faça suas Alterações

Implemente suas alterações seguindo os [padrões de código](#padrões-de-código) do projeto.

**Durante o desenvolvimento:**

```bash
# Execute o linter para formatar o código
./vendor/bin/sail pint

# Execute os testes
./vendor/bin/sail test

# Compile os assets (se necessário)
./vendor/bin/sail npm run build
```

### 6. Commit suas Alterações

Faça commits pequenos e descritivos seguindo os [padrões de commit](#padrões-de-commit):

```bash
# Adicione os arquivos modificados
git add .

# Faça o commit
git commit -m "feat: adiciona filtro por bairro na busca"
```

### 7. Push para o GitHub

Envie suas alterações para o seu fork:

```bash
git push origin tipo/descricao-curta
```

### 8. Abra um Pull Request

1. Acesse seu fork no GitHub
2. Você verá um banner indicando que sua branch está à frente do repositório original
3. Clique em **Compare & pull request**
4. Preencha o template do PR com:
   - Descrição clara das alterações
   - Issue relacionada (se houver)
   - Screenshots (se aplicável)
   - Checklist de verificação
5. Clique em **Create pull request**

## Padrões de Código

Este projeto utiliza o [Laravel Pint](https://laravel.com/docs/pint) com o preset **PER** (PHP Evolution Recommendations).

### Regras Principais

| Regra | Descrição |
|-------|-----------|
| `declare_strict_types` | Tipos estritos obrigatórios em todos os arquivos |
| `final_class` | Classes devem ser `final` por padrão |
| `strict_comparison` | Usar comparações estritas (`===` e `!==`) |
| `void_return` | Métodos sem retorno devem declarar `void` |
| `yoda_style` | Condições no estilo Yoda (`null === $var`) |
| `ordered_imports` | Imports ordenados alfabeticamente |

### Boas Práticas

- Use tipos estritos em todos os arquivos (`declare(strict_types=1)`)
- Escreva testes para novas funcionalidades
- Mantenha os métodos pequenos e focados
- Documente funções complexas
- Evite comentários óbvios - o código deve ser autoexplicativo
- Siga os padrões já existentes no projeto

### Executando o Linter

```bash
# Formatar código automaticamente
./vendor/bin/sail pint

# Verificar sem corrigir
./vendor/bin/sail pint --test
```

## Padrões de Commit

Utilizamos [Conventional Commits](https://www.conventionalcommits.org/pt-br/) para mensagens de commit:

```
tipo(escopo): descrição curta

corpo opcional com mais detalhes

rodapé opcional (ex: closes #123)
```

### Tipos de Commit

| Tipo | Descrição |
|------|-----------|
| `feat` | Nova funcionalidade |
| `fix` | Correção de bug |
| `docs` | Documentação |
| `style` | Formatação (não afeta a lógica) |
| `refactor` | Refatoração de código |
| `test` | Adição ou correção de testes |
| `chore` | Tarefas de manutenção |

### Exemplos

```bash
feat(mapa): adiciona zoom automático ao selecionar local
fix(auth): corrige erro de sessão expirada no login
docs(readme): atualiza instruções de instalação
refactor(location): extrai lógica de cache para service
test(adapter): adiciona testes para KoboAdapter
```

## Revisão de Código

Após abrir o Pull Request:

1. Aguarde a revisão de um mantenedor
2. Responda aos comentários e faça ajustes se necessário
3. Mantenha o PR atualizado com a branch principal:

```bash
git fetch upstream
git rebase upstream/main
git push -f origin tipo/descricao-curta
```

4. Após aprovação, um mantenedor fará o merge

---

**Dúvidas?** Abra uma [issue](../../issues) ou entre em contato com os mantenedores.

Obrigado por contribuir!
