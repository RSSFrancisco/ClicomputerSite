# Identidad web de Clicomputer

Primera aplicación al sitio a partir de las imágenes originales proporcionadas por el propietario.

## Logotipo

- `assets/img/clicomputer-logo.png`: logo completo original, sin cambiar letras, colores ni proporciones.
- `assets/img/clicomputer-symbol.png`: símbolo original para el favicon.
- `app/Views/partials/brand.php`: componente compartido por navegación y pie de página.

En el tema claro, la cabecera y el pie muestran el logo original sobre blanco. En el tema oscuro, ambas superficies se oscurecen y el logo se presenta en blanco mediante un filtro CSS y el modo de mezcla `screen`, que integra el fondo de la imagen con la superficie. El archivo original se conserva sin cambios y no se duplica; la variante responde al atributo `data-bs-theme` que controla el selector existente. El tamaño se adapta con CSS, sin estirar ni recortar el logo; sus dimensiones explícitas evitan saltos durante la carga. Una versión SVG original permitiría sustituir el PNG en el futuro, sin redibujar la marca.

El símbolo también se declara como `logo` de la organización en los datos estructurados de `app/Core/Seo.php`.

## Paleta de interfaz

Los colores base son una aproximación visual al símbolo suministrado; no sustituyen especificaciones oficiales de marca.

| Uso | Color |
| --- | --- |
| Azul de identidad, detalles y formas | `#009ED3` |
| Gris del símbolo y textos secundarios | `#505E65` |
| Botones y enlaces sobre fondos claros | `#006F98` |
| Enlaces y acentos sobre fondos oscuros | `#49BCE2` |
| Texto principal en tema claro | `#273A43` |
| Fondo claro | `#FAFCFB` |
| Fondo oscuro | `#111B20` |

El azul de los botones es más oscuro para permitir texto blanco legible. Los colores se centralizan en `css/variables.css`; las reglas de aplicación están en `css/brand.css`.

## Tipografía y composición

El nombre conserva las letras del archivo original. Georgia acompaña los títulos como referencia al carácter con serifas del logo; no pretende reemplazar su tipografía. Inter se utiliza en párrafos, navegación y formularios. Georgia no requiere una descarga adicional.

La interfaz utiliza bordes discretos, botones de color sólido y esquinas poco redondeadas. Un pequeño rombo en la presentación retoma la geometría del símbolo. Se reducen los degradados y resplandores del diseño anterior.

La Tierra, los cinco servicios interactivos y el cohete siguen siendo recursos secundarios de la presentación. Mantienen sus controles de movimiento reducido y su comportamiento con jQuery.

## Voz y siguiente etapa

Textos directos sobre el servicio y la zona de atención. Evitar promesas sin condiciones o cifras que no se puedan respaldar. Para continuar con el portafolio y «Nosotros», incorporar fotografías propias, capturas de trabajos y datos confirmados de proyectos; esas pruebas no se sustituyen con imágenes inventadas.

La tarjeta para compartir enlaces (`social-card.png`) conserva por ahora el diseño anterior: esta primera aplicación cubre el sitio y su favicon.
