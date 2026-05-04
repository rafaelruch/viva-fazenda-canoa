<?php
/**
 * Title: Formulário de captação
 * Slug: viva-fazenda-canoa/capture
 * Categories: viva-fazenda-canoa
 * Description: Formulário na metade da página
 */
?>
<!-- wp:html -->
<section class="capture" id="contato">
  <div class="capture__inner">
    <div class="capture__text">
      <p class="eyebrow">Agende sua visita</p>
      <h2>O privilégio de viver como<br>em um resort, <em>todos os dias.</em></h2>
      <p>Preencha o formulário e um consultor exclusivo retorna em instantes com todas as informações — plantas, disponibilidade, condições e agendamento de visita presencial ou virtual.</p>
      <ul class="capture__benefits">
        <li>
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
          Resposta no mesmo dia útil
        </li>
        <li>
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
          Visita presencial ou tour virtual guiado
        </li>
        <li>
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
          Condições exclusivas da fase atual
        </li>
        <li>
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
          Lotes a partir de R$ 419.000,00
        </li>
      </ul>
    </div>

    <form class="lead-form" id="lead-form" novalidate>
      <h3>Fale com um consultor</h3>
      <p>Receba informações completas em breve com nosso consultor.</p>

      <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;pointer-events:none" aria-hidden="true">

      <label class="field"><span>Nome</span><input type="text" name="nome" required autocomplete="name" placeholder="Seu nome completo"></label>
      <label class="field"><span>WhatsApp</span><input type="tel" name="telefone" required autocomplete="tel" placeholder="(62) 9 0000-0000"></label>
      <label class="field"><span>E-mail <span class="field__opt">(opcional)</span></span><input type="email" name="email" autocomplete="email" placeholder="voce@email.com"></label>
      <label class="field"><span>Interesse</span>
        <select name="interesse" required>
          <option value="">Selecione...</option>
          <option>Lote frente-lago</option>
          <option>Lote vista-lago</option>
          <option>Lote bosque</option>
          <option>Agendamento de visita</option>
          <option>Simulação de pagamento</option>
          <option>Ainda não decidi</option>
        </select>
      </label>

      <button type="submit" class="btn btn--corten btn--block btn--lg">
        Quero conhecer a Fazenda Canoa
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
      </button>
      <p class="lead-form__disclaimer">Ao enviar, você concorda em receber contato comercial da Fazenda Canoa.</p>
      <p class="lead-form__success" hidden>✓ Recebemos seu contato! Em breve um consultor entra em contato com você.</p>
    </form>
  </div>
</section>
<!-- /wp:html -->
