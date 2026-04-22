<?php
/**
 * Title: Estilo de Vida
 * Slug: viva-fazenda-canoa/lifestyle
 * Categories: viva-fazenda-canoa
 * Description: Seção editorial lifestyle com carrossel de fotos
 */
?>
<!-- wp:html -->
<section class="lifestyle" id="lifestyle">
  <div class="lifestyle__text">
    <p class="eyebrow">Estilo de vida</p>
    <h2>Imagine acordar e caminhar<br>até <em>o seu próprio lago.</em></h2>
    <p>O sol nasce no espelho d'água. A manhã começa na ciclovia que corta a mata preservada. O fim de tarde é no deck de cumaru, com um vinho da Costa Cave — a vinícola do próprio condomínio. Aos fins de semana, receber amigos no Beach Club, embarcar na marina, jogar tênis na quadra oficial.</p>
    <p><strong>Aqui, não se trata apenas de adquirir um lote. Mas de acessar um novo estilo de vida.</strong></p>
    <a href="#contato" class="btn btn--corten">Quero conhecer</a>
  </div>
  <div class="lifestyle__gallery" id="lifestyle-carousel">
    <div class="lf-track" id="lf-track">
      <figure class="lf-slide"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/fotos/' ) ); ?>08.jpg" alt="Pavilhão Social com paisagismo" loading="lazy"></figure>
      <figure class="lf-slide"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/fotos/' ) ); ?>03.jpg" alt="Estar com vista para o lago" loading="lazy"></figure>
      <figure class="lf-slide"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/fotos/' ) ); ?>12.jpg" alt="Vinícola Costa Cave" loading="lazy"></figure>
      <figure class="lf-slide"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/fotos/' ) ); ?>20.jpg" alt="Interior premium" loading="lazy"></figure>
      <figure class="lf-slide"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/fotos/' ) ); ?>22.jpg" alt="Vista aérea do condomínio" loading="lazy"></figure>
    </div>
    <div class="lf-dots" id="lf-dots" role="tablist" aria-label="Navegação do carrossel"></div>
  </div>
</section>
<!-- /wp:html -->
