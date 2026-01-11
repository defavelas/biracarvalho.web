<img src="https://biracarvalho.observatoriodefavelas.org.br/assets/images/logo.svg" alt="Programa Bira Carvalho" width="156">

# Programa Bira Carvalho

Plataforma de mapeamento de acessibilidade no território da Favela da Maré, Rio de Janeiro, Brasil.

## Sobre o Projeto

O **Programa Bira Carvalho** é uma plataforma digital de mapeamento de acessibilidade que coleta e exibe dados sobre locais acessíveis e não acessíveis no território da Favela da Maré, Rio de Janeiro, Brasil.

A plataforma integra-se com o [KoboToolbox](https://www.kobotoolbox.org/) para coleta de dados via pesquisas móveis, permitindo que agentes de campo registrem informações sobre acessibilidade de locais diretamente pelo celular.

### Principais Objetivos

- Mapear locais acessíveis e não acessíveis no território da Favela da Maré
- Facilitar a coleta de dados em campo via dispositivos móveis
- Disponibilizar um mapa interativo com informações de acessibilidade
- Permitir moderação e aprovação de dados coletados

## Mapeamento de Acessibilidade

*Seção em construção - será preenchida futuramente com detalhes sobre a metodologia de mapeamento.*

## Estrutura do Repositório

```
biracarvalho.web/
├── app/
│   ├── Adapter/           # Transformação de dados externos
│   │   └── Kobo/          # Adapters para API KoboToolbox
│   ├── Console/           # Comandos Artisan
│   │   └── Commands/      # Comandos de importação Kobo
│   ├── Enum/              # Enumerações
│   │   └── Location/      # Tipos de localização (acessível/não acessível)
│   ├── Http/              # Controllers, Middleware, Requests
│   ├── Jobs/              # Jobs para processamento em background
│   ├── Livewire/          # Componentes Livewire (UI reativa)
│   ├── Models/            # Modelos Eloquent
│   └── Services/          # Lógica de negócio
├── config/                # Arquivos de configuração
├── database/              # Migrations e seeders
├── public/                # Assets públicos
├── resources/             # Views, CSS, JS
├── routes/                # Definição de rotas
├── storage/               # Arquivos gerados
└── tests/                 # Testes automatizados
```

### Detalhes das Pastas Principais

#### `app/Adapter/`

Camada de transformação de dados para integração com APIs externas.

- **`Kobo/AccessibleLocationAdapter.php`** - Transforma dados de pesquisas de locais acessíveis
- **`Kobo/NonAccessibleLocationAdapter.php`** - Transforma dados de pesquisas de locais não acessíveis

#### `app/Enum/`

Enumerações tipadas para valores constantes.

- **`Location/Type.php`** - Define os tipos de localização:
  - `ACCESSIBLE` - Local acessível
  - `NON_ACCESSIBLE` - Local não acessível

#### `app/Models/`

Modelos Eloquent que representam as entidades do sistema.

- **`Location.php`** - Representa um local mapeado (nome, coordenadas, tipo, status)
- **`Image.php`** - Imagens associadas aos locais
- **`Info.php`** - Informações adicionais (perguntas e respostas das pesquisas)
- **`User.php`** - Usuários administrativos

#### `app/Services/`

Camada de serviços com lógica de negócio.

- **`LocationService.php`** - Gerenciamento de locais (busca, cache, estatísticas)
- **`Kobo/KoboClient.php`** - Cliente HTTP para API KoboToolbox
- **`Kobo/AccessibleLocationsService.php`** - Busca dados de locais acessíveis
- **`Kobo/NonAccessibleLocationsService.php`** - Busca dados de locais não acessíveis
- **`Kobo/AccessibleLocationsProcess.php`** - Processamento de importação de locais acessíveis
- **`Kobo/NonAccessibleLocationsProcess.php`** - Processamento de importação de locais não acessíveis

## Como Executar o Projeto

### Requisitos

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

### Instalação

1. Clone o repositório:

```bash
git clone https://github.com/seu-usuario/biracarvalho.web.git
cd biracarvalho.web
```

2. Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

3. Instale as dependências via Composer (usando Docker):

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

4. Inicie os containers com Laravel Sail (Recomendado):

```bash
./vendor/bin/sail up -d
```

5. Gere a chave da aplicação:

```bash
./vendor/bin/sail artisan key:generate
```

6. Execute as migrations:

```bash
./vendor/bin/sail artisan migrate
```

7. Compile os assets:

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

### Acessando a Aplicação

- **Aplicação:** http://localhost
- **Painel Admin:** http://localhost/admin/login

### Comandos Úteis

```bash
# Iniciar containers
./vendor/bin/sail up -d

# Parar containers
./vendor/bin/sail down

# Acessar o container
./vendor/bin/sail shell

# Executar comandos Artisan
./vendor/bin/sail artisan <comando>

# Importar dados do KoboToolbox
./vendor/bin/sail artisan kobo:import-all

# Executar testes
./vendor/bin/sail test
```

### Variáveis de Ambiente

Configure as seguintes variáveis no arquivo `.env`:

```env
# KoboToolbox
KOBO_API_URL=https://kf.kobotoolbox.org
KOBO_API_TOKEN=token-da-sua-conta-kobotoolbox
```

## Funcionalidades

### Mapa Interativo

Visualização de locais acessíveis e não acessíveis em um mapa interativo. Os locais são exibidos com marcadores coloridos de acordo com seu tipo (verde para acessível, laranja para não acessível).

### Integração KoboToolbox

Sincronização automática de dados coletados via pesquisas móveis no KoboToolbox. Suporta dois tipos de formulários: locais acessíveis e locais não acessíveis.

### Sistema de Moderação

Painel administrativo para aprovação e rejeição de locais. Locais coletados ficam pendentes até serem aprovados por um moderador.

### Busca e Filtros

Sistema de busca em tempo real com filtros por tipo de local (acessível/não acessível). A busca é realizada por nome, descrição e autores.

### Gerenciamento de Imagens

Upload e exibição de fotos associadas aos locais. As imagens são sincronizadas automaticamente a partir das pesquisas do KoboToolbox.

### Sistema de Filas

Processamento em background para importação de dados. Os jobs de importação são executados de forma assíncrona para não bloquear a aplicação.

## Code Linting

O projeto utiliza o [Laravel Pint](https://laravel.com/docs/pint) para formatação e padronização de código.

### Executando o Pint

```bash
./vendor/bin/sail pint
```

Ou para verificar sem corrigir:

```bash
./vendor/bin/sail pint --test
```

### Configuração PER

O projeto utiliza o preset **PER** (PHP Evolution Recommendations), que segue as recomendações de evolução do PHP para código moderno e limpo.

Além do preset PER, as seguintes regras adicionais estão configuradas:

| Regra | Descrição |
|-------|-----------|
| `declare_strict_types` | Exige declaração de tipos estritos em todos os arquivos |
| `final_class` | Classes devem ser `final` por padrão |
| `strict_comparison` | Uso obrigatório de comparações estritas (`===` e `!==`) |
| `void_return` | Métodos sem retorno devem declarar `void` |
| `yoda_style` | Condições no estilo Yoda (`null === $var`) |
| `ordered_class_elements` | Elementos da classe ordenados (traits, constantes, propriedades, métodos) |
| `ordered_imports` | Imports ordenados alfabeticamente |
| `use_arrow_functions` | Preferência por arrow functions quando possível |

A configuração completa pode ser encontrada no arquivo `pint.json`.

## Como Contribuir

Agradecemos seu interesse em contribuir com o Programa Bira Carvalho!

Consulte o arquivo [CONTRIBUTING.md](CONTRIBUTING.md) para instruções detalhadas sobre como contribuir com o projeto, incluindo:

- Como fazer fork e clonar o repositório
- Como criar branches e fazer commits
- Como abrir Pull Requests
- Padrões de código e boas práticas
- Tipos de contribuição aceitos

## Código de Conduta

Este projeto adota um Código de Conduta para garantir um ambiente acolhedor e respeitoso para todos os participantes.

Consulte o arquivo [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) para mais detalhes.

## Segurança

Se você descobrir uma vulnerabilidade de segurança, **não** abra uma issue pública. Consulte o arquivo [SECURITY.md](SECURITY.md) para instruções sobre como reportar vulnerabilidades de forma responsável.

## Acessibilidade Digital

Como uma plataforma de mapeamento de acessibilidade, este projeto segue rigorosamente os padrões de acessibilidade web [WCAG 2.2](https://www.w3.org/WAI/standards-guidelines/wcag/) nível AA.

Consulte o arquivo [ACCESSIBILITY.md](ACCESSIBILITY.md) para:

- Padrões e diretrizes seguidas
- Boas práticas para contribuidores
- Checklist de verificação
- Ferramentas de teste recomendadas

## Licença

Este projeto é um software de código aberto licenciado sob a [GNU Affero General Public License v3.0](https://www.gnu.org/licenses/agpl-3.0.html).
