# Clicomputer México — PHP 8 MVC

Sitio en PHP 8 con controladores, modelos de contenido, una plantilla maestra y vistas parciales. El servidor entrega el HTML completo; no hay un paso de compilación. Se conservan las URLs públicas terminadas en `.html`, aunque ahora las atiende PHP.

## Estructura

| Ubicación | Responsabilidad |
| --- | --- |
| `index.php` | Punto de entrada y envío de la respuesta HTTP. |
| `app/Core/Application.php` | Resolución de rutas y métodos HTTP. |
| `app/Controllers/` | Páginas, sitemap, robots y preparación del contacto. |
| `app/Models/` | Datos del sitio, servicios y validación de solicitudes. |
| `config/site.php` | Dominio principal, teléfono, correo y páginas generales. |
| `data/services.php` | Contenido y metadatos de las cinco páginas nuevas de servicio. |
| `app/Views/layouts/master.php` | Plantilla maestra del sitio. |
| `app/Views/layouts/cv.php` | Plantilla del CV que conserva su diseño de impresión. |
| `app/Views/partials/` | SEO, navegación, ruta de navegación y pie de página. |
| `app/Views/pages/` | Vistas de inicio, servicios, seguridad, proyectos, contacto y CV. |
| `css/`, `js/`, `assets/` | Recursos públicos; CSS y JavaScript incluyen versiones de caché. |
| `router.php` | Enrutador exclusivo para el servidor local de PHP. |

Los modelos usan archivos PHP con datos, sin base de datos ni dependencias de Composer. Para cambiar un dato de contacto, edita `config/site.php`; los parciales y los datos estructurados lo toman del mismo lugar. El contenido de las páginas aparece en la respuesta inicial y los enlaces funcionan sin JavaScript.

## Vista previa y pruebas

Requiere PHP 8. Las pruebas se ejecutaron con PHP 8.5. El entorno de pruebas usa las extensiones DOM y SimpleXML; el sitio no las necesita para servir páginas.

```sh
php -S 127.0.0.1:8780 router.php
```

Abre `http://127.0.0.1:8780/`. No uses un servidor de archivos estáticos para esta versión.

En otra terminal:

```sh
php tests/run.php
node --test tests/contact-form.test.cjs
php tests/http.php
```

Node solo se usa para las pruebas del comportamiento JavaScript; no se necesita en el alojamiento. Las pruebas PHP verifican rutas, respuestas HTTP, títulos, URLs canónicas, sitemap, enlaces, marcado estructurado, formularios y escape de datos.

`tests/http.php` requiere la vista previa en el puerto 8780 y la extensión cURL. En macOS también inicia una instancia aislada de Apache en el puerto 8781 para verificar `.htaccess` con archivos de prueba; la detiene al terminar. No modifica el servicio Apache del sistema ni el sitio publicado.

## Contacto

El formulario prepara un borrador para WhatsApp al número configurado. El visitante debe abrir el enlace, revisar el texto y enviarlo en WhatsApp. No se muestra una confirmación de envío ni se borran los campos al preparar el borrador.

Con JavaScript, la preparación ocurre en el navegador. Sin JavaScript, `POST /contacto/preparar` valida los campos y vuelve a mostrar el formulario con el enlace preparado. PHP devuelve 422 cuando hay errores y conserva los valores con escape HTML. Esta respuesta no se almacena en caché y está marcada `noindex`.

El servidor no envía mensajes, no almacena solicitudes y no requiere credenciales de WhatsApp. Los enlaces directos a teléfono y correo siguen disponibles. Para enviar formularios directamente por correo habría que configurar un servicio de correo y su backend; `mailto:` abre el cliente de correo del visitante.

## Publicar en el alojamiento actual

1. Conserva un respaldo del sitio publicado y de su `.htaccess` actual. Integra las reglas del proveedor si ya existen, sin sobrescribirlas a ciegas.
2. Comprueba que el alojamiento tiene PHP 8 habilitado, Apache 2.4, `mod_rewrite` y autorización para usar las directivas de `.htaccess`.
3. Sube `app/`, `config/`, `data/`, `assets/`, `css/`, `js/` e `index.php`. Activa `.htaccess` después de subir todos los archivos. No hace falta subir `.git`, el ZIP del respaldo, pruebas, documentación ni `router.php`.
4. Los HTML anteriores y los archivos físicos `sitemap.xml` y `robots.txt` se sustituyen por las rutas MVC. Las reglas fuerzan esas rutas a PHP incluso si quedaron copias antiguas en el alojamiento; conviene retirarlas del despliegue tras guardar el respaldo.
5. Verifica que inicio y servicios devuelven 200; una ruta inexistente debe devolver 404. HTTP, el dominio sin `www`, `/index.html` y `/index.php` deben redirigir a la versión canónica en HTTPS, conservando las rutas y parámetros.
6. Si el proveedor termina HTTPS en un proxy/CDN, debe adaptar la detección de HTTPS a su configuración de confianza antes de activar estas reglas, para evitar bucles.
7. Comprueba el favicon, la imagen para compartir enlaces y una solicitud de contacto. Las carpetas internas deben devolver 403 en Apache. La vista previa local devuelve 404 para las mismas rutas privadas.

La vista previa con `php -S` no aplica `.htaccess`; la normalización del dominio y HTTPS se verifica por separado en Apache. El proyecto no ha sido publicado automáticamente.

## Seguimiento SEO

- Usa la propiedad existente de Search Console o verifica el dominio con el registro DNS que Google indique. No se ha inventado un código de verificación.
- Envía `https://www.clcomputer.com/sitemap.xml`. Se genera a partir de las nueve páginas públicas y omite fechas artificiales de actualización.
- Después de publicar, inspecciona inicio, seguridad y una página nueva; comprueba el HTML, la URL canónica y la indexación.
- Ejecuta PageSpeed Insights en móvil y escritorio. Si existen datos de campo, revisa LCP ≤ 2.5 s, INP ≤ 200 ms y CLS ≤ 0.1 en el percentil 75. No se han atribuido puntuaciones a esta versión sin medirla publicada.
- Guarda una línea de base de clics, impresiones, consultas y páginas de entrada para comparar después del nuevo rastreo.
- Revisa el Perfil de Empresa: categoría, servicios, teléfono, ubicación o zona de atención, horarios, fotos y reseñas auténticas.

Se añadió marcado `Organization`, `WebSite`, `WebPage`, `Service` y rutas de navegación según la página. No se han inventado direcciones de calle, reseñas, precios ni perfiles sociales. El marcado específico de negocio local puede completarse cuando estén confirmados los datos necesarios.

El logotipo original no estaba en el repositorio ni en el respaldo: se utiliza el nombre como marca tipográfica, un favicon propio y una imagen PNG de 1200 × 630 para compartir. `assets/img/social-card.svg` es el original editable de esa imagen.

Se conservaron las cifras y descripciones de proyectos que ya figuraban en el sitio. Conviene confirmar que reflejan trabajos y disponibilidad reales. Córdoba, Veracruz, el teléfono y el correo proceden del contenido existente.

Referencias: [SEO con JavaScript](https://developers.google.com/search/docs/crawling-indexing/javascript/javascript-seo-basics), [URL canónica](https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls), [sitemaps](https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap), [Web Vitals](https://web.dev/articles/vitals), [Perfil de Empresa](https://support.google.com/business/answer/7091?hl=es).
