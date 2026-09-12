function prefersReducedMotion() {
    try {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    } catch {
        return false;
    }
}

function spawnEmoji(symbol, x, y, fontSize = 24) {
    const el = document.createElement('div');
    el.textContent = symbol;
    el.style.cssText = `position:fixed;left:${x}px;top:${y}px;font-size:${fontSize}px;z-index:200;pointer-events:none;line-height:1;transform:translate(-50%,-50%);will-change:transform,opacity;`;
    document.body.appendChild(el);

    return el;
}

/**
 * Anima un cohete emoji desde el botón "Añadir al carrito" clickeado hasta el
 * ícono del carrito en el nav (marcado con [data-cart-target]).
 */
export function flyRocketToCart(originEl) {
    if (!originEl || prefersReducedMotion()) {
        return;
    }

    const target = document.querySelector('[data-cart-target]');

    if (!target) {
        return;
    }

    const originRect = originEl.getBoundingClientRect();
    const targetRect = target.getBoundingClientRect();

    const startX = originRect.left + originRect.width / 2;
    const startY = originRect.top + originRect.height / 2;
    const endX = targetRect.left + targetRect.width / 2;
    const endY = targetRect.top + targetRect.height / 2;
    const dx = endX - startX;
    const dy = endY - startY;

    const rocket = spawnEmoji('🚀', startX, startY, 26);

    const animation = rocket.animate([
        { transform: 'translate(-50%, -50%) rotate(45deg) scale(1)', opacity: 1, offset: 0 },
        { transform: `translate(calc(-50% + ${dx * 0.5}px), calc(-50% + ${dy * 0.5 - 60}px)) rotate(45deg) scale(1.15)`, opacity: 1, offset: 0.55 },
        { transform: `translate(calc(-50% + ${dx}px), calc(-50% + ${dy}px)) rotate(45deg) scale(0.25)`, opacity: 0.4, offset: 1 },
    ], { duration: 700, easing: 'ease-in' });

    const cleanup = () => rocket.remove();
    animation.onfinish = cleanup;
    animation.oncancel = cleanup;
    setTimeout(cleanup, 900);

    const bump = target.animate([
        { transform: 'scale(1)' },
        { transform: 'scale(1.3)' },
        { transform: 'scale(1)' },
    ], { duration: 320, delay: 620, easing: 'ease-out' });
    bump.onfinish = () => target.style.removeProperty('transform');
}

/**
 * Anima un cohete que "explota" en confeti sobre el elemento indicado
 * (usado al quitar un producto del carrito).
 */
export function explodeRocket(originEl) {
    if (!originEl || prefersReducedMotion()) {
        return;
    }

    const rect = originEl.getBoundingClientRect();
    const x = rect.left + rect.width / 2;
    const y = rect.top + rect.height / 2;

    const rocket = spawnEmoji('🚀', x, y, 22);

    const shrink = rocket.animate([
        { transform: 'translate(-50%, -50%) scale(1)', opacity: 1 },
        { transform: 'translate(-50%, -50%) scale(1.4)', opacity: 0 },
    ], { duration: 200, easing: 'ease-in' });

    const finishExplosion = () => {
        rocket.remove();

        const particles = ['💥', '✨', '🔥'];

        particles.forEach((symbol, index) => {
            const count = 3;

            for (let i = 0; i < count; i++) {
                const particle = spawnEmoji(symbol, x, y, 15);
                const angle = (Math.PI * 2 * (index * count + i)) / (particles.length * count);
                const distance = 36 + Math.random() * 24;
                const dx = Math.cos(angle) * distance;
                const dy = Math.sin(angle) * distance;

                const anim = particle.animate([
                    { transform: 'translate(-50%, -50%) scale(1)', opacity: 1 },
                    { transform: `translate(calc(-50% + ${dx}px), calc(-50% + ${dy}px)) scale(0.3)`, opacity: 0 },
                ], { duration: 480, easing: 'ease-out' });

                const cleanup = () => particle.remove();
                anim.onfinish = cleanup;
                setTimeout(cleanup, 600);
            }
        });
    };

    shrink.onfinish = finishExplosion;
    setTimeout(finishExplosion, 250);
}
