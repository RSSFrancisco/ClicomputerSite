/**
 * Clicomputer México — Mouse Follower (Cat)
 * Un tierno gatito que sigue el cursor con un ligero retraso
 */
const MouseFollower = (() => {
  'use strict';

  let cat = null;
  let mouseX = 0;
  let mouseY = 0;
  let catX = 0;
  let catY = 0;
  const speed = 0.08; // Velocidad de seguimiento (0.1 = 10% de la distancia por frame)

  function createCat() {
    cat = document.createElement('div');
    cat.id = 'mouse-cat';
    cat.innerHTML = '🐱'; // Emoji de gatito
    document.body.appendChild(cat);
  }

  function updatePosition() {
    // Interpolación lineal (LERP) para un movimiento suave
    catX += (mouseX - catX) * speed;
    catY += (mouseY - catY) * speed;

    if (cat) {
      // Ajustamos un poco para que no tape el cursor exactamente (offset)
      cat.style.transform = `translate3d(${catX + 15}px, ${catY + 15}px, 0)`;
      
      // Rotación sutil según la dirección del movimiento
      const deltaX = mouseX - catX;
      const rotation = deltaX * 0.1;
      cat.style.transform += ` rotate(${rotation}deg)`;
    }

    requestAnimationFrame(updatePosition);
  }

  function onMouseMove(e) {
    mouseX = e.clientX;
    mouseY = e.clientY;
  }

  function init() {
    // Solo activar en desktop o si el usuario no tiene desactivadas las animaciones
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    
    createCat();
    window.addEventListener('mousemove', onMouseMove);
    updatePosition();
  }

  return { init };
})();
