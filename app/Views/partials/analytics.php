<?php
$analyticsId = $site['analytics_id'] ?? '';
$analyticsEnabled = preg_match('/^G-[A-Z0-9]{4,20}$/D', $analyticsId)
    && ($_SERVER['HTTP_HOST'] ?? '') === parse_url($site['url'], PHP_URL_HOST)
    && !str_contains($page['robots'] ?? '', 'noindex');
if ($analyticsEnabled):
$analyticsConfig = ['id' => $analyticsId, 'page' => $canonical, 'title' => $page['title']];
?>
<script type="application/json" id="analyticsConfig"><?= json_encode($analyticsConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?></script>
<div class="container"><button type="button" class="analytics-settings" id="analyticsSettings" hidden>Preferencias de medición</button></div>
<section class="analytics-consent" id="analyticsConsent" aria-labelledby="analyticsConsentTitle" hidden>
  <h2 id="analyticsConsentTitle" class="h5">Medición opcional de visitas</h2>
  <p>Con tu permiso usamos Google Analytics para medir visitas y clics de contacto. No enviamos el texto que escribes en el formulario. Puedes cambiar tu elección en el pie de página.</p>
  <p><a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Privacidad de Google</a></p>
  <div class="d-flex gap-2 flex-wrap">
    <button type="button" class="btn btn-outline-custom" id="analyticsDecline">Continuar sin medición</button>
    <button type="button" class="btn btn-primary-custom" id="analyticsAccept">Permitir medición</button>
  </div>
</section>
<script defer src="<?= e(asset('js/components/contact-tracking.js')) ?>"></script>
<?php endif ?>
