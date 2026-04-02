document.addEventListener('DOMContentLoaded', () => {
    // 1. Intersection Observer for Scroll Reveal
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15
    };
    
    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target); // Reveal only once
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.reveal-item').forEach(item => {
        observer.observe(item);
    });

    // 2. Parallax Effect for Hero Background
    const heroBg = document.getElementById('parallax-bg');
    if(heroBg) {
        window.addEventListener('scroll', () => {
            let scrolled = window.scrollY;
            // Translating the background slower than the scroll speed
            heroBg.style.transform = `scale(1.05) translateY(${scrolled * 0.4}px)`;
        });
    }

    // 3. Magnetic Button Effect
    const magneticBtns = document.querySelectorAll('.btn-magnetic');
    magneticBtns.forEach(btn => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            // Calculate mouse distance from center bounds
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            // Limit the movement
            btn.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px)`;
        });
        
        btn.addEventListener('mouseleave', () => {
            // Reset to center
            btn.style.transform = 'translate(0px, 0px)';
        });
    });
});
