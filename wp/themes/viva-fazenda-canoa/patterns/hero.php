<?php
/**
 * Title: Hero — Vídeo fullscreen
 * Slug: viva-fazenda-canoa/hero
 * Categories: viva-fazenda-canoa
 * Description: Hero cinematográfico com vídeo mashup
 */
?>
<!-- wp:html -->
<section class="hero" id="top">
  <div class="hero__video-wrap">
    <video class="hero__video" autoplay loop muted playsinline preload="auto" poster="<?php echo esc_url( get_theme_file_uri( 'assets/video/' ) ); ?>hero-poster.jpg">
      <source src="<?php echo esc_url( get_theme_file_uri( 'assets/video/' ) ); ?>hero-mashup.mp4" type="video/mp4">
    </video>
    <div class="hero__scrim"></div>
    <div class="hero__grain"></div>
  </div>

  <div class="hero__content">
    <p class="hero__eyebrow">Condomínio-Reserva · Lago Corumbá IV · Silvânia, GO</p>
    <h1 class="hero__title">Parece cenário<br>de filme.<br><em>Mas pode ser seu.</em></h1>
    <p class="hero__sub">Lago privativo, vinícola própria e refúgio de elite — sua experiência exclusiva às margens do lago.</p>
    <a href="#contato" class="btn btn--corten btn--lg hero__cta">
      Quero viver assim
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
    </a>
  </div>

  <a href="#para-quem" class="hero__scroll" aria-label="Rolar">
    <span>Descubra</span>
    <span class="hero__scroll-line"></span>
  </a>
</section>
<!-- /wp:html -->
