<?php $whatsapp = $whatsappUrl ?? ('https://wa.me/' . ltrim($site['telephone'], '+')); ?>
<section class="contact-section section-padding" id="contacto">
  <div class="container">
    <div class="section-header">
      <span class="section-badge"><i class="bi bi-whatsapp" aria-hidden="true"></i> Contacto</span>
      <?php if (($standaloneContact ?? false) || ($page['standalone_contact'] ?? false)): ?>
        <h1>Cotiza tu servicio en Córdoba, Veracruz</h1>
      <?php else: ?>
        <h2>Hablemos de tu <span class="text-gradient">proyecto</span></h2>
      <?php endif ?>
      <p>Escríbenos por WhatsApp y cuéntanos cómo podemos ayudarte.</p>
    </div>
    <?php if ($contactNotice): ?><p class="alert alert-info" role="status"><?= e($contactNotice) ?></p><?php endif ?>
    <div class="row g-4">
      <div class="col-lg-7"><div class="contact-info-card">
        <h3 class="h4">Atención directa por WhatsApp</h3>
        <p>Abre el chat, escribe tu nombre y el servicio que necesitas, y envía tu mensaje. Te responderemos por ahí mismo.</p>
        <a id="contactWhatsApp" class="btn btn-primary-custom w-100 mb-3" href="<?= e($whatsapp) ?>" target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp" aria-hidden="true"></i> Escribir por WhatsApp</a>
        <p><?= e($site['telephone_display']) ?></p>
        <p class="mb-0"><?= e($site['hours_display']) ?>.</p>
      </div></div>
      <div class="col-lg-5"><div class="contact-info-card">
        <h3 class="h4">También puedes contactarnos</h3>
        <div class="contact-info-item"><div class="contact-info-icon"><i class="bi bi-telephone-fill" aria-hidden="true"></i></div>
          <div><h4 class="h6">Llámanos</h4><p><a href="tel:<?= e($site['telephone']) ?>"><?= e($site['telephone_display']) ?></a></p></div></div>
        <div class="contact-info-item"><div class="contact-info-icon"><i class="bi bi-envelope-fill" aria-hidden="true"></i></div>
          <div><h4 class="h6">Correo electrónico</h4><p><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></p><p>Abre tu aplicación de correo.</p></div></div>
        <div class="contact-info-item mb-0"><div class="contact-info-icon"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i></div>
          <div><h4 class="h6">Zona de atención</h4><p><?= e($site['locality'] . ', ' . $site['region']) ?>, México</p><p><?= e($site['service_mode']) ?>.</p></div></div>
      </div></div>
    </div>
  </div>
</section>
