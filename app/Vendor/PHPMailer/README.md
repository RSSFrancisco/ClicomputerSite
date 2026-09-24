# PHPMailer 7.1.1

Distribución mínima oficial: `src/PHPMailer.php`, `src/SMTP.php` y `src/Exception.php`, sin modificaciones.

- Fuente: https://github.com/PHPMailer/PHPMailer/releases/tag/v7.1.1
- Archivo: https://codeload.github.com/PHPMailer/PHPMailer/tar.gz/refs/tags/v7.1.1
- Licencia: LGPL-2.1, conservada en `LICENSE`; `COMMITMENT` incluido.

Se incluye en `app/` para que el despliegue de cPanel no necesite Composer. El autoloader del proyecto carga el namespace `PHPMailer\PHPMailer` desde `src/`. Para actualizar, copiar estos mismos archivos desde una versión oficial fijada, conservar la licencia y ejecutar las pruebas SMTP y de contacto.
