# SEO local: implementación y seguimiento

Servicios prioritarios confirmados por el propietario: instalación de cámaras, soporte técnico y creación de páginas web. Zona de atención publicada: Córdoba, Veracruz.

## Cambios del sitio

- Inicio y tarjetas priorizan los tres servicios confirmados.
- Las tres páginas explican alcance, preparación de cotización, variables de precio, calendario y soporte sin inventar tarifas o plazos.
- Nueva página de contacto, índice de guías, tres guías y dos páginas de portafolio basadas en material existente. El sitemap contiene 16 páginas. No se creó un tercer caso sin evidencia adicional.
- Datos estructurados `ContactPage`, `Article` para guías, jerarquía de rutas de navegación y horario de contacto consistente. Se conserva `Organization`: no se inventa una dirección física para completar `LocalBusiness`.
- Se retiran del inicio las cifras de proyectos/clientes, la certificación genérica y el soporte 24/7 pendientes de respaldo. El horario de atención proviene de `config/site.php`.
- Navegación con posición estable desde el primer render; fallback móvil si Bootstrap no carga. Sin JavaScript continúa expandida.
- Logo WebP de 745 × 216: aproximadamente 8 KB frente a 80 KB del original, conservado. Bootstrap 5.3.8, Bootstrap Icons 1.11.3 e Inter se sirven localmente. Inter usa `font-display: optional`; la tipografía monoespaciada utiliza fuentes del sistema.

## Línea de base del 21 de septiembre de 2026

PageSpeed móvil, inicio publicado antes del despliegue:

| Métrica | Resultado |
| --- | --- |
| Rendimiento | 61/100 |
| Accesibilidad | 98/100 |
| Recomendaciones | 100/100 |
| SEO técnico Lighthouse | 100/100 |
| LCP | 3.6 s |
| CLS | 0.586 |
| TBT | 0 ms |

[Informe de referencia](https://pagespeed.web.dev/analysis/https-www-clcomputer-com/l6mosh64q8?form_factor=mobile). Son datos de laboratorio, no datos de usuarios reales ni una medida de posicionamiento. PageSpeed no tenía datos de campo. La API pública devolvió 429; el informe se obtuvo en la interfaz web.

Search Console mostró la propiedad de dominio verificada en el perfil de Brave indicado por el propietario. Se corrigió el nombre del TXT de `clcomputer.clcomputer.com` a `clcomputer.com` con autorización; TTL 3600 para coincidir con los otros TXT del mismo nombre. Google confirmó la recepción de `https://www.clcomputer.com/sitemap.xml`. Los informes iniciales seguían procesándose.

## Medición de contactos

La propiedad de Clicomputer se confirmó en el perfil de Brave: `G-C5KG6QTZJP`. Está configurada en `config/site.php`; `CLICOMPUTER_GA4_ID` permite reemplazarla y el valor `disabled` suspende la integración. Solo se activa en `www.clcomputer.com`, no en vistas previas locales.

Se desactivó **Medición mejorada** en la propiedad confirmada. Si se cambia de propiedad, desactivar **Medición mejorada** del flujo web (especialmente clics salientes, formularios, búsqueda del sitio e historial). Es necesario porque esas funciones pueden recoger automáticamente URLs de WhatsApp que incluyen el borrador. Esta implementación envía únicamente eventos explícitos y no necesita esos eventos automáticos.

La etiqueta de Google solo se carga después del consentimiento del visitante. Eventos:

| Evento | Qué significa |
| --- | --- |
| `page_view` | Visita con URL canónica, sin parámetros ni fragmentos; del referrer solo se conserva el origen para atribución. |
| `contact_click` | Clic a WhatsApp, teléfono o correo; parámetro `contact_method`. |
| `quote_prepared` | Borrador válido preparado en el navegador. No significa mensaje enviado. |

No se incluyen nombre, correo, teléfono del visitante, mensaje ni URL de destino. Las páginas de búsqueda, errores y respuestas de formulario no cargan medición. Registrar `contact_method` como dimensión personalizada en GA4 y verificar DebugView/tiempo real antes de darla por activa. El consentimiento es revocable en el pie; rechazar detiene nuevos eventos, aunque no borra datos históricos ya enviados.

## Pendientes que requieren información o acceso externo

- Perfil de Empresa: confirmar ficha, categoría, ubicación pública o negocio de área de servicio, horario y fotografías. No publicar dirección, reseñas, certificaciones o cifras no confirmadas.
- GA4: validar llegada de eventos después de publicar; propiedad y configuración del flujo ya confirmadas.
- Completar casos con permiso del cliente, fecha, alcance y resultados verificables. No atribuir las fotografías industriales al proyecto de 32 cámaras sin confirmación.
- Revisar Search Console cuando termine de procesar: estado de indexación, consultas y páginas. Comparar periodos de 28 días, excluyendo consultas de marca al valorar adquisición.
- Repetir PageSpeed en las páginas prioritarias después de publicar; comparar métricas, no solo la puntuación.

## Próximos contenidos y revisión mensual

1. Mes 2: caso real de instalación y guía de mantenimiento de cámaras basada en los equipos que se atienden.
2. Mes 3: caso real de soporte y guía de mantenimiento de páginas web con el alcance contratado.
3. Solicitar reseñas auténticas al terminar trabajos; no se enviaron mensajes a clientes durante esta implementación.
4. Registrar consultas orgánicas, clics, contactos y cotizaciones recibidas en cada periodo. Definir metas numéricas tras contar con línea de base.
