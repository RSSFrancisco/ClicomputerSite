<?php $whatsapp = 'https://wa.me/' . ltrim($site['telephone'], '+'); ?>
<section class="contact-section section-padding" id="contacto">
  <div class="container">
    <div class="section-header">
      <span class="section-badge"><i class="bi bi-envelope" aria-hidden="true"></i> Contacto</span>
      <?php if (($standaloneContact ?? false) || ($page['standalone_contact'] ?? false)): ?>
        <h1>Cotiza tu servicio en Córdoba, Veracruz</h1>
      <?php else: ?>
        <h2>Hablemos de tu <span class="text-gradient">proyecto</span></h2>
      <?php endif ?>
      <p>Cuéntanos qué necesitas. Puedes preparar tu solicitud aquí y enviarla por WhatsApp.</p>
    </div>
    <div class="row g-4">
      <div class="col-lg-7"><div class="contact-form-card">
        <p><a href="<?= e($whatsapp) ?>">Abrir una conversación en WhatsApp</a> o <a href="mailto:<?= e($site['email']) ?>">escribirnos por correo</a>.</p>
        <form id="contactForm" method="post" action="/contacto/preparar#contacto" data-whatsapp="<?= e($whatsapp) ?>" aria-describedby="contactHelp">
          <p id="contactHelp">Al terminar, abre WhatsApp, revisa el mensaje y pulsa Enviar. Los campos marcados con * son obligatorios.</p>
          <?php if ($errors): ?><p class="alert alert-danger" role="alert">Revisa los campos indicados antes de preparar tu mensaje.</p><?php endif ?>
          <div class="row g-3">
            <?php foreach ([['name','contactName','Nombre completo *','text','name',80], ['email','contactEmail','Email (opcional)','email','email',120], ['phone','contactPhone','Teléfono (opcional)','tel','tel',25]] as [$key,$id,$label,$type,$autocomplete,$limit]): ?>
            <div class="col-md-6">
              <label class="form-label" for="<?= e($id) ?>"><?= e($label) ?></label>
              <input type="<?= e($type) ?>" class="form-control" id="<?= e($id) ?>" name="<?= e($key) ?>" autocomplete="<?= e($autocomplete) ?>" maxlength="<?= $limit ?>" value="<?= e($values[$key] ?? '') ?>" <?= $key === 'name' ? 'required' : '' ?> <?= isset($errors[$key]) ? 'aria-invalid="true" aria-describedby="error-' . e($key) . '"' : '' ?>>
              <?php if (isset($errors[$key])): ?><p class="text-danger" id="error-<?= e($key) ?>"><?= e($errors[$key]) ?></p><?php endif ?>
            </div>
            <?php endforeach ?>
            <div class="col-md-6">
              <label class="form-label" for="contactService">Servicio *</label>
              <select class="form-select" id="contactService" name="service" required <?= isset($errors['service']) ? 'aria-invalid="true" aria-describedby="error-service"' : '' ?>>
                <option value="" disabled <?= empty($values['service']) ? 'selected' : '' ?>>Selecciona un servicio</option>
                <?php foreach (App\Models\ContactRequest::SERVICES as $service): ?>
                  <option <?= ($values['service'] ?? '') === $service ? 'selected' : '' ?>><?= e($service) ?></option>
                <?php endforeach ?>
              </select>
              <?php if (isset($errors['service'])): ?><p class="text-danger" id="error-service"><?= e($errors['service']) ?></p><?php endif ?>
            </div>
            <div class="col-12">
              <label class="form-label" for="contactMessage">Mensaje *</label>
              <textarea class="form-control" id="contactMessage" name="message" rows="5" maxlength="1000" required <?= isset($errors['message']) ? 'aria-invalid="true" aria-describedby="error-message"' : '' ?>><?= e($values['message'] ?? '') ?></textarea>
              <?php if (isset($errors['message'])): ?><p class="text-danger" id="error-message"><?= e($errors['message']) ?></p><?php endif ?>
            </div>
            <div class="col-12"><button type="submit" class="btn btn-primary-custom w-100">Preparar mensaje de WhatsApp</button></div>
          </div>
        </form>
        <div id="formAlerts" role="status" aria-live="polite" class="mt-3"><?= $draftUrl ? 'Tu mensaje está preparado. Ábrelo en WhatsApp, revísalo y pulsa Enviar para hacérnoslo llegar.' : '' ?></div>
        <a id="preparedWhatsApp" class="btn btn-outline-custom mt-2" target="_blank" rel="noopener noreferrer" <?= $draftUrl ? 'href="' . e($draftUrl) . '"' : 'hidden' ?>>Abrir WhatsApp con mi solicitud</a>
      </div></div>
      <div class="col-lg-5"><div class="contact-info-card">
        <h3 class="h4">Información de contacto</h3>
        <div class="contact-info-item"><div class="contact-info-icon"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i></div>
          <div><h4 class="h6">Zona de atención</h4><p><?= e($site['locality'] . ', ' . $site['region']) ?>, México</p></div></div>
        <div class="contact-info-item"><div class="contact-info-icon"><i class="bi bi-telephone-fill" aria-hidden="true"></i></div>
          <div><h4 class="h6">Teléfono</h4><p><a href="tel:<?= e($site['telephone']) ?>"><?= e($site['telephone_display']) ?></a></p></div></div>
        <div class="contact-info-item"><div class="contact-info-icon"><i class="bi bi-envelope-fill" aria-hidden="true"></i></div>
          <div><h4 class="h6">Email</h4><p><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></p></div></div>
        <div class="contact-info-item"><div class="contact-info-icon"><i class="bi bi-whatsapp" aria-hidden="true"></i></div>
          <div><h4 class="h6">WhatsApp</h4><p><a href="<?= e($whatsapp) ?>">Envíanos un mensaje</a></p></div></div>
        <p><?= e($site['hours_display']) ?>. Consulta la disponibilidad para tu proyecto.</p>
      </div></div>
    </div>
  </div>
</section>
