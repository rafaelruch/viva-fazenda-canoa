# 📦 Releases — Pacotes prontos para instalação

Esta pasta contém os **pacotes ZIP prontos** para upload via WordPress admin. Use estes ao invés de baixar o repositório inteiro.

## Arquivos

| Arquivo | Versão | O que é | Tamanho |
|---|---|---|---|
| `viva-fazenda-canoa-theme.zip` | **1.2.0** | Tema (block theme emocional, hero com vídeo, Cormorant + Manrope) | ~24 MB |
| `lfc-opcoes-plugin.zip`        | **1.0.1** | Plugin de opções + leads + webhook ImobMeet (compartilhado com LP1) | ~10 KB |

### Mudanças na v1.2.0 (tema) e v1.0.1 (plugin) — 2026-04-24

- **Tema v1.2.0 (feature):**
  - Máscara de telefone BR auto-aplicada nos inputs `[type="tel"]` enquanto o usuário digita: `(62) 99999-9999`. Funciona no form principal `capture.php` e no modal `capture-modal`.
  - Animação smooth de entrada do estado de sucesso (`.modal__success` e `.lead-form__success`): fade + slide-up no container, scale-pop no ícone, fade escalonado no título e parágrafo. Respeita `prefers-reduced-motion`.
- **Tema v1.1.1 (patch):** corrigido bug visual em que o `modal__success` aparecia visível mesmo com atributo `hidden` no HTML.
- **Tema v1.1.0:** removido o redirect para WhatsApp após submit dos formulários. Agora o form mostra apenas a confirmação ("Recebemos seu contato! Em breve um consultor entra em contato com você.") e o lead vai pro CRM via webhook do plugin.
- **Plugin v1.0.1:** webhook ImobMeet hardcoded como default (`LFC_DEFAULT_WEBHOOK_URL`) com fallback. Leads chegam ao CRM mesmo sem configurar nada no admin.

---

## 🚀 Como instalar (passo a passo)

### 1. Plugin (instalar primeiro)

1. Baixe o arquivo **[lfc-opcoes-plugin.zip](lfc-opcoes-plugin.zip?raw=1)**
2. WordPress Admin → **Plugins** → **Adicionar novo** → **Carregar plugin**
3. Escolha o ZIP baixado → **Instalar agora** → **Ativar plugin**
4. Se já houver versão antiga instalada, o WordPress pergunta se quer **substituir**. Clica em substituir — as opções salvas no banco são preservadas.

### 2. Tema (instalar depois do plugin)

1. Baixe o arquivo **[viva-fazenda-canoa-theme.zip](viva-fazenda-canoa-theme.zip?raw=1)**
2. WordPress Admin → **Aparência** → **Temas** → **Adicionar novo tema** → **Carregar tema**
3. Escolha o ZIP baixado → **Instalar agora** → **Ativar**

> ⚠️ **Limite de upload:** Se sua hospedagem limita uploads acima de 25 MB, o tema pode dar erro. Solução: use FTP/SFTP/cPanel para extrair o ZIP diretamente em `wp-content/themes/`, OU peça ao suporte para aumentar `upload_max_filesize` no PHP.

### 3. Configuração (1 minuto)

1. Admin → **Configurações** → **Fazenda Canoa**
2. Revise os defaults (WhatsApp, e-mail, horário) e salve
3. O **webhook ImobMeet** já vem como default — não precisa mexer no campo "URL do webhook" a menos que queira mandar para outro destino

### 4. Validar o fluxo

1. Acesse a home (LP é o `front-page.html`)
2. Submeta o formulário com nome + WhatsApp + interesse
3. **Esperado:** form trava, mensagem "✓ Recebemos seu contato! Em breve um consultor entra em contato com você." aparece. **Nenhuma aba nova abre.**
4. Admin → menu lateral **Leads** → o lead aparece com `webhook_status: dispatched`
5. Confere no painel ImobMeet se chegou

---

## ❌ NÃO faça assim

- ❌ Baixar o repositório inteiro como ZIP do GitHub e mandar pra `wp-content/themes/`
- ❌ Fazer `git clone` do repositório dentro de `wp-content/themes/`

---

## ✅ Faça assim

- ✅ Baixe APENAS os ZIPs desta pasta
- ✅ Instale via WP admin (upload de ZIP) — esses ZIPs já têm o slug WP correto (`viva-fazenda-canoa/` e `lfc-opcoes/`) na raiz interna
