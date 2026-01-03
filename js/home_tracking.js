/**
 * js/home_tracking.js
 * Manejo de eventos GA4 para HomeNueva
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. IntersectionObserver para Scroll Tracking por Bloques
    const observerOptions = {
        root: null, // viewport
        rootMargin: '0px',
        threshold: 0.5 // El bloque debe estar al 50% visible
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const blockId = entry.target.id;
                let sectionName = '';

                // Mapear ID a nombre de sección para GA4
                switch(blockId) {
                    case 'block-yesterday': sectionName = 'yesterday'; break;
                    case 'block-latest': sectionName = 'latest'; break;
                    case 'block-last-articles': sectionName = 'last_articles'; break;
                    case 'block-summaries': sectionName = 'summaries'; break;
                    case 'block-featured': sectionName = 'featured'; break;
                }

                if (sectionName) {
                    // Disparar evento GA4
                    if (typeof gtag === 'function') {
                        gtag("event", "home_scroll_section", {
                            section: sectionName
                        });
                        // console.log("GA4 Scroll:", sectionName);
                    }
                    
                    // Dejar de observar este bloque una vez registrado
                    observer.unobserve(entry.target);
                }
            }
        });
    }, observerOptions);

    // Observar todos los bloques
    const blocks = document.querySelectorAll('.home-block');
    blocks.forEach(block => {
        observer.observe(block);
    });

});

// 2. Función global para Clics (llamada desde onclick en HTML)
window.trackHomeClick = function(zone, id, title, category, pos) {
    if (typeof gtag === 'function') {
        gtag("event", "click_home_article", {
            zone: zone,
            article_id: id,
            article_title: title,
            position_list: pos,
            category: category
        });
    } else {
        // Fallback / Debug
        console.log("GA4 Click:", { zone, id, title, category, pos });
    }
};
