# Diretrizes de Acessibilidade

O **Programa Bira Carvalho** é uma plataforma de mapeamento de acessibilidade, e por isso, a acessibilidade digital é uma prioridade fundamental do projeto. Este documento descreve os padrões e práticas que seguimos para garantir que a plataforma seja acessível a todos os usuários.

## Índice

- [Padrões Seguidos](#padrões-seguidos)
- [Princípios WCAG (POUR)](#princípios-wcag-pour)
- [Diretrizes para Contribuidores](#diretrizes-para-contribuidores)
  - [HTML Semântico](#html-semântico)
  - [Atributos ARIA](#atributos-aria)
  - [Navegação por Teclado](#navegação-por-teclado)
  - [Leitores de Tela](#leitores-de-tela)
  - [Cores e Contraste](#cores-e-contraste)
  - [Formulários Acessíveis](#formulários-acessíveis)
  - [Imagens e Mídia](#imagens-e-mídia)
  - [Movimento e Animações](#movimento-e-animações)
- [Componentes do Projeto](#componentes-do-projeto)
- [Ferramentas de Teste](#ferramentas-de-teste)
- [Recursos e Referências](#recursos-e-referências)

## Padrões Seguidos

Este projeto segue as diretrizes internacionais de acessibilidade web:

| Padrão | Versão | Nível | Descrição |
|--------|--------|-------|-----------|
| [WCAG](https://www.w3.org/WAI/standards-guidelines/wcag/) | 2.2 | AA | Web Content Accessibility Guidelines |
| [WAI-ARIA](https://www.w3.org/WAI/standards-guidelines/aria/) | 1.2 | - | Accessible Rich Internet Applications |
| [ATAG](https://www.w3.org/WAI/standards-guidelines/atag/) | 2.0 | - | Authoring Tool Accessibility Guidelines |

### Conformidade WCAG 2.2 Nível AA

O nível AA é o padrão legal exigido na maioria das legislações, incluindo:
- Lei Brasileira de Inclusão (LBI - Lei 13.146/2015)
- European Accessibility Act (EAA)
- Americans with Disabilities Act (ADA)

## Princípios WCAG (POUR)

As WCAG são organizadas em 4 princípios fundamentais:

### 1. Perceptível (Perceivable)

O conteúdo deve ser apresentado de formas que os usuários possam perceber.

- Fornecer alternativas textuais para conteúdo não textual
- Fornecer legendas e alternativas para mídia
- Criar conteúdo que possa ser apresentado de diferentes formas
- Facilitar a visualização e audição do conteúdo

### 2. Operável (Operable)

Os componentes de interface devem ser operáveis por todos.

- Tornar todas as funcionalidades acessíveis via teclado
- Dar tempo suficiente para ler e usar o conteúdo
- Não projetar conteúdo que cause convulsões
- Ajudar os usuários a navegar e encontrar conteúdo
- Facilitar o uso de entradas além do teclado

### 3. Compreensível (Understandable)

A informação e operação da interface devem ser compreensíveis.

- Tornar o texto legível e compreensível
- Fazer as páginas aparecerem e operarem de forma previsível
- Ajudar os usuários a evitar e corrigir erros

### 4. Robusto (Robust)

O conteúdo deve ser robusto o suficiente para ser interpretado por diversas tecnologias.

- Maximizar compatibilidade com tecnologias assistivas atuais e futuras

## Diretrizes para Contribuidores

### HTML Semântico

Use elementos HTML semânticos apropriados:

```html
<!-- ✅ Correto -->
<header>...</header>
<nav>...</nav>
<main>...</main>
<article>...</article>
<section>...</section>
<aside>...</aside>
<footer>...</footer>

<!-- ❌ Evitar -->
<div class="header">...</div>
<div class="navigation">...</div>
```

**Hierarquia de cabeçalhos:**

```html
<!-- ✅ Correto - hierarquia lógica -->
<h1>Título Principal</h1>
  <h2>Seção</h2>
    <h3>Subseção</h3>

<!-- ❌ Evitar - pular níveis -->
<h1>Título</h1>
  <h3>Subseção</h3>
```

**Idioma da página:**

```html
<!-- Sempre declarar o idioma -->
<html lang="pt-BR">
```

### Atributos ARIA

Use ARIA para melhorar a acessibilidade quando HTML semântico não for suficiente:

**Landmarks (marcos de navegação):**

```html
<div role="main">...</div>
<div role="navigation">...</div>
<div role="complementary">...</div>
<div role="search">...</div>
```

**Estados e propriedades:**

```html
<!-- Botões expansíveis -->
<button aria-expanded="false" aria-controls="menu">Menu</button>

<!-- Campos obrigatórios -->
<input aria-required="true" aria-label="Nome completo">

<!-- Elementos ocultos -->
<div aria-hidden="true">Conteúdo decorativo</div>

<!-- Regiões dinâmicas -->
<div aria-live="polite" aria-atomic="true">Status atualizado</div>
```

**Padrões de componentes:**

```html
<!-- Modal/Dialog -->
<div role="dialog" aria-modal="true" aria-labelledby="titulo" aria-describedby="descricao">
  <h2 id="titulo">Título do Modal</h2>
  <p id="descricao">Descrição do conteúdo</p>
</div>

<!-- Combobox/Select customizado -->
<div role="combobox" aria-expanded="false" aria-haspopup="listbox">
  <input aria-autocomplete="list">
  <ul role="listbox">
    <li role="option" aria-selected="false">Opção 1</li>
  </ul>
</div>

<!-- Toggle/Switch -->
<button role="switch" aria-checked="false">Ativar notificações</button>
```

### Navegação por Teclado

Todos os elementos interativos devem ser acessíveis via teclado:

**Teclas padrão:**

| Tecla | Ação |
|-------|------|
| `Tab` | Navegar para o próximo elemento focável |
| `Shift + Tab` | Navegar para o elemento anterior |
| `Enter` / `Space` | Ativar botões e links |
| `Escape` | Fechar modais e menus |
| `Arrow Keys` | Navegar em menus, listas e sliders |
| `Home` / `End` | Ir para início/fim de listas |

**Focus states (estados de foco):**

```css
/* ✅ Sempre fornecer indicador de foco visível */
button:focus {
  outline: 2px solid #653089;
  outline-offset: 2px;
}

/* ✅ Usar focus-visible para melhor UX */
button:focus-visible {
  outline: 2px solid #653089;
  outline-offset: 2px;
}

/* ❌ Nunca remover outline sem alternativa */
button:focus {
  outline: none; /* Não faça isso! */
}
```

**Skip links (links de salto):**

```html
<!-- Primeiro elemento do body -->
<a href="#main-content" class="sr-only focus:not-sr-only">
  Pular para o conteúdo principal
</a>
```

**Trap de foco em modais:**

```html
<!-- Usando Alpine.js -->
<div x-trap="modalAberto">
  <!-- Conteúdo do modal -->
</div>
```

### Leitores de Tela

**Conteúdo apenas para leitores de tela:**

```html
<!-- Classe sr-only (screen reader only) -->
<span class="sr-only">Descrição para leitores de tela</span>
```

```css
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
```

**Regiões ao vivo (live regions):**

```html
<!-- Anúncios educados (não interrompem) -->
<div aria-live="polite">Resultados atualizados: 5 locais encontrados</div>

<!-- Anúncios assertivos (interrompem) -->
<div aria-live="assertive">Erro: campo obrigatório</div>

<!-- Status de carregamento -->
<div aria-busy="true">Carregando...</div>
```

### Cores e Contraste

**Requisitos de contraste WCAG 2.2 AA:**

| Tipo de Texto | Contraste Mínimo |
|---------------|------------------|
| Texto normal (< 18pt) | 4.5:1 |
| Texto grande (≥ 18pt ou 14pt bold) | 3:1 |
| Componentes de UI e gráficos | 3:1 |

**Cores do projeto:**

| Cor | Hex | Uso |
|-----|-----|-----|
| Primary | `#653089` | Elementos principais, foco |
| Secondary | `#ced842` | Destaques, botões secundários |
| Accent | `#d55229` | Alertas, ações importantes |
| Success | `#00C950` | Locais acessíveis |
| Warning | `#F59E0C` | Locais não acessíveis |

**Não dependa apenas de cor:**

```html
<!-- ✅ Correto - cor + ícone + texto -->
<span class="text-red-500">
  <svg><!-- ícone de erro --></svg>
  Erro: Campo obrigatório
</span>

<!-- ❌ Evitar - apenas cor -->
<span class="text-red-500">Campo obrigatório</span>
```

### Formulários Acessíveis

**Labels associados:**

```html
<!-- ✅ Correto - label associado -->
<label for="email">E-mail</label>
<input type="email" id="email" name="email">

<!-- ✅ Alternativa - aria-label -->
<input type="search" aria-label="Buscar locais">

<!-- ❌ Evitar - sem label -->
<input type="email" placeholder="Digite seu e-mail">
```

**Campos obrigatórios:**

```html
<label for="nome">
  Nome
  <span aria-label="obrigatório" class="text-red-500">*</span>
</label>
<input type="text" id="nome" required aria-required="true">
```

**Mensagens de erro:**

```html
<label for="email">E-mail</label>
<input
  type="email"
  id="email"
  aria-invalid="true"
  aria-describedby="email-erro"
>
<span id="email-erro" role="alert" class="text-red-500">
  Por favor, insira um e-mail válido
</span>
```

**Texto de ajuda:**

```html
<label for="senha">Senha</label>
<input type="password" id="senha" aria-describedby="senha-ajuda">
<span id="senha-ajuda" class="text-gray-500">
  Mínimo de 8 caracteres
</span>
```

### Imagens e Mídia

**Texto alternativo:**

```html
<!-- Imagens informativas -->
<img src="mapa.png" alt="Mapa mostrando 15 locais acessíveis na Maré">

<!-- Imagens decorativas -->
<img src="decoracao.png" alt="" role="presentation">

<!-- Imagens complexas -->
<figure>
  <img src="grafico.png" alt="Gráfico de barras" aria-describedby="grafico-desc">
  <figcaption id="grafico-desc">
    Descrição detalhada do gráfico...
  </figcaption>
</figure>
```

**Slideshow de imagens:**

```html
<div role="region" aria-label="Galeria de imagens do local">
  <img
    src="foto1.jpg"
    alt="Fachada do estabelecimento - Imagem 1 de 5"
  >
  <button aria-label="Imagem anterior">←</button>
  <button aria-label="Próxima imagem">→</button>
</div>
```

### Movimento e Animações

**Respeitar preferências do usuário:**

```css
/* Reduzir movimento para usuários que preferem */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

**Controles de animação:**

```html
<!-- Fornecer controle para pausar animações -->
<button aria-label="Pausar slideshow automático">⏸</button>
```

## Componentes do Projeto

O projeto inclui componentes acessíveis pré-construídos:

| Componente | Arquivo | Recursos de Acessibilidade |
|------------|---------|---------------------------|
| Input | `components/input.blade.php` | Labels, erro com role="alert", aria-describedby |
| Select | `components/select.blade.php` | Padrão combobox, navegação por teclado |
| Modal | `components/modal.blade.php` | role="dialog", aria-modal, trap de foco |
| Toggle | `components/toggle-button.blade.php` | role="switch", aria-checked |
| Slideshow | `components/image-slideshow.blade.php` | Navegação por teclado, alt dinâmico |
| Mapa | `js/components/map.js` | Navegação por teclado, anúncios para leitores de tela |

## Ferramentas de Teste

### Testes Automatizados

| Ferramenta | Tipo | Link |
|------------|------|------|
| axe DevTools | Extensão do navegador | [axe](https://www.deque.com/axe/) |
| WAVE | Extensão do navegador | [WAVE](https://wave.webaim.org/) |
| Lighthouse | Chrome DevTools | Integrado no Chrome |
| Pa11y | CLI | [Pa11y](https://pa11y.org/) |

### Testes Manuais

- **Navegação por teclado**: Navegue pela página usando apenas Tab, Enter, Escape e setas
- **Leitor de tela**: Teste com NVDA (Windows), VoiceOver (Mac/iOS) ou TalkBack (Android)
- **Zoom**: Verifique a página com zoom de 200% e 400%
- **Contraste**: Use ferramentas como [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- **Modo de alto contraste**: Teste no Windows High Contrast Mode

### Checklist de Verificação

Antes de submeter um PR, verifique:

- [ ] Todos os elementos interativos são acessíveis via teclado
- [ ] Focus states são visíveis em todos os elementos focáveis
- [ ] Imagens têm texto alternativo apropriado
- [ ] Formulários têm labels associados
- [ ] Cores têm contraste suficiente (4.5:1 para texto)
- [ ] ARIA é usado corretamente quando necessário
- [ ] Conteúdo dinâmico é anunciado para leitores de tela
- [ ] A página funciona com zoom de 200%
- [ ] Não há dependência apenas de cor para transmitir informação

## Recursos e Referências

### Documentação Oficial

- [WCAG 2.2 (W3C)](https://www.w3.org/TR/WCAG22/)
- [WAI-ARIA Practices](https://www.w3.org/WAI/ARIA/apg/)
- [MDN Web Accessibility](https://developer.mozilla.org/pt-BR/docs/Web/Accessibility)

### Checklists

- [WebAIM WCAG 2 Checklist](https://webaim.org/standards/wcag/checklist)
- [A11Y Project Checklist](https://www.a11yproject.com/checklist/)

### Ferramentas

- [axe DevTools](https://www.deque.com/axe/)
- [WAVE Web Accessibility Evaluator](https://wave.webaim.org/)
- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- [Accessible Colors](https://accessible-colors.com/)

### Leitura Adicional

- [Inclusive Components](https://inclusive-components.design/)
- [A11Y Style Guide](https://a11y-style-guide.com/style-guide/)
- [The A11Y Project](https://www.a11yproject.com/)

---

**Dúvidas sobre acessibilidade?** Abra uma [issue](../../issues) com a label `accessibility`.
