<section class="security-gallery section-padding" id="instalaciones" aria-labelledby="security-gallery-title">
  <div class="container">
    <span class="section-badge">Trabajo en campo</span>
    <div class="accordion security-installations">
      <div class="accordion-item">
        <h2 class="accordion-header" id="security-gallery-title">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#industrialPhotos" aria-expanded="false" aria-controls="industrialPhotos">
            <span class="security-installations-icon" aria-hidden="true"><i class="bi bi-building"></i></span>
            <span class="security-installations-label">
              <span class="security-installations-title">Instalaciones industriales</span>
              <span class="security-installations-summary">Videovigilancia en entornos industriales · <?= e(count($photos)) ?> fotografías</span>
            </span>
          </button>
        </h2>
        <div id="industrialPhotos" class="accordion-collapse collapse" role="region" aria-labelledby="security-gallery-title">
          <div class="accordion-body">
            <p class="mb-4">Cámaras, montaje y monitoreo en un entorno industrial. Selecciona una fotografía para ver los detalles de la instalación.</p>
            <div class="security-gallery-grid">
              <?php foreach ($photos as $photo): ?>
                <?php
                $path = 'assets/img/seguridad/' . $photo['file'];
                $full = asset($path . '-960.webp');
                $small = asset($path . '-480.webp');
                ?>
                <figure class="security-photo security-photo--<?= e($photo['id']) ?>">
                  <a class="security-photo-link" href="<?= e($full) ?>" data-security-photo aria-label="<?= e('Ampliar fotografía: ' . $photo['title']) ?>">
                    <img src="<?= e($small) ?>" srcset="<?= e($small) ?> 480w, <?= e($full) ?> 960w"
                      sizes="(max-width: 767px) calc(100vw - 24px), (max-width: 991px) 50vw, <?= $photo['id'] === 'monitoreo' ? '60vw' : '40vw' ?>"
                      width="<?= e($photo['width']) ?>" height="<?= e($photo['height']) ?>"
                      alt="<?= e($photo['alt']) ?>" loading="lazy" decoding="async">
                    <span class="security-photo-expand" aria-hidden="true"><i class="bi bi-arrows-fullscreen"></i></span>
                  </a>
                  <figcaption>
                    <h3><?= e($photo['title']) ?></h3>
                    <p><?= e($photo['description']) ?></p>
                  </figcaption>
                </figure>
              <?php endforeach ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="modal fade security-photo-modal" id="securityPhotoModal" tabindex="-1" aria-labelledby="securityPhotoTitle" aria-describedby="securityPhotoCaption" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title h5" id="securityPhotoTitle">Fotografía de instalación</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar fotografía"></button>
      </div>
      <div class="modal-body">
        <img class="security-photo-full" alt="" hidden>
        <p id="securityPhotoCaption" class="mt-3 mb-0"></p>
      </div>
    </div>
  </div>
</div>
