<?php
require_once 'includes/init.php';
require_once 'includes/header.php';

// Safe image fallbacks if they forget to upload specific images, but we know they exist from our ls command.
$heroImg = "images/about_hero_premium.png"; 
$storyImg = "images/story_premium.png";
?>

<style>
/* ── Modern Minimalist Aesthetic ── */
.about-wrapper {
    background-color: #050505;
    color: #ffffff;
    font-family: 'Kanit', sans-serif;
    overflow-x: hidden;
}

/* Base Typography */
.about-wrapper h1, 
.about-wrapper h2 {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    text-transform: uppercase;
    line-height: 1.1;
    letter-spacing: -0.02em;
}

/* 1. HERO SECTION */
.about-hero {
    min-height: 85vh;
    display: flex;
    align-items: center;
    position: relative;
    padding: 0 5%;
}

.about-hero-bg {
    position: absolute;
    inset: 0;
    z-index: 1;
}

.about-hero-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    filter: brightness(0.4) saturate(0.8);
}

.about-hero-content {
    position: relative;
    z-index: 2;
    max-width: 800px;
    padding-top: 100px; /* Offset for fixed header */
}

.about-hero-label {
    font-family: 'Outfit', sans-serif;
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 4px;
    color: #b8a080;
    margin-bottom: 24px;
    display: block;
    text-transform: uppercase;
}

.about-hero h1 {
    font-size: clamp(3rem, 7vw, 6rem);
    margin-bottom: 30px;
    color: #ffffff;
}

.about-hero p {
    font-weight: 300;
    font-size: clamp(1rem, 1.5vw, 1.2rem);
    color: #d4d4d4;
    max-width: 500px;
    line-height: 1.6;
}

/* 2. PHILOSOPHY SECTION */
.about-philosophy {
    padding: 15rem 5%;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8rem;
    align-items: center;
    background: #020202;
    max-width: 1400px;
    margin: 0 auto;
}

.about-philo-text h2 {
    font-size: clamp(2rem, 4vw, 3.5rem);
    margin-bottom: 3rem;
    color: #ffffff;
}

.about-philo-text p {
    color: #a3a3a3;
    font-weight: 300;
    font-size: 1.1rem;
    line-height: 2;
    margin-bottom: 2rem;
}

.about-philo-image {
    position: relative;
    width: 100%;
    aspect-ratio: 4/5;
    overflow: hidden;
}

.about-philo-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(30%) contrast(1.1);
}

/* 3. CTA ENDING */
.about-ending {
    padding: 10rem 5%;
    text-align: center;
    background: #050505;
}

.about-ending h2 {
    font-size: clamp(2.5rem, 5vw, 4.5rem);
    margin-bottom: 3rem;
    color: #ffffff;
}

.about-btn {
    display: inline-block;
    border: 1px solid #ffffff;
    color: #ffffff;
    padding: 18px 48px;
    text-decoration: none;
    font-family: 'Outfit', sans-serif;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-size: 0.95rem;
    font-weight: 600;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    background: transparent;
}

.about-btn:hover {
    background: #ffffff;
    color: #000000;
}

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
    .about-philosophy {
        grid-template-columns: 1fr;
        padding: 8rem 5%;
        gap: 4rem;
    }
    
    .about-philo-image {
        order: -1; /* Image appears first on mobile for better flow */
    }
    
    .about-hero {
        min-height: 70vh;
    }
}

/* ── Light Mode Overrides ── */
body.light-mode .about-wrapper { background-color: var(--bg-primary); color: var(--text-color); }
body.light-mode .about-hero h1 { color: var(--text-color); }
body.light-mode .about-hero p { color: var(--text-secondary); }
body.light-mode .about-philosophy { background: var(--bg-secondary); }
body.light-mode .about-philo-text h2 { color: var(--text-color); }
body.light-mode .about-philo-text p { color: var(--text-secondary); }
body.light-mode .about-ending { background: var(--bg-primary); }
body.light-mode .about-ending h2 { color: var(--text-color); }
body.light-mode .about-btn { border-color: var(--text-color); color: var(--text-color); }
body.light-mode .about-btn:hover { background: var(--text-color); color: var(--bg-primary); }
</style>

<div class="about-wrapper">

    <!-- HERO SECTION -->
    <section class="about-hero">
        <div class="about-hero-bg">
            <img src="<?= htmlspecialchars($heroImg) ?>" alt="XIVEX Streetwear">
        </div>
        <div class="about-hero-content">
            <span class="about-hero-label">Established 2025</span>
            <h1><?= __('abt_hero_h1') ?? 'UNFILTERED EXPRESSION' ?></h1>
            <p><?= __('abt_hero_p') ?? 'We don\'t follow trends. We dictate them through the purity of raw aesthetics and uncompromising quality.' ?></p>
        </div>
    </section>

    <!-- PHILOSOPHY SECTION -->
    <section class="about-philosophy">
        <div class="about-philo-text">
            <h2><?= __('abt_vision_h2') ?? 'REDEFINING THE NORM' ?></h2>
            <p><?= __('abt_vision_p') ?? 'XIVEX is born from the chaos of the streets and refined in the silence of minimalist design. Our vision is to strip away the unnecessary, leaving only what truly matters: premium materials, perfect silhouettes, and unquestionable attitude.' ?></p>
            <p><?= __('abt_craft_p') ?? 'Every thread is chosen with purpose. Every stitch is a deliberate act of rebellion against the ordinary. We create garments that stand the test of time, both physically and stylistically.' ?></p>
        </div>
        <div class="about-philo-image">
            <img src="<?= htmlspecialchars($storyImg) ?>" alt="XIVEX Philosophy">
        </div>
    </section>

    <!-- CTA ENDING SECTION -->
    <section class="about-ending">
        <h2><?= __('abt_cta_h2') ?? 'ELEVATE YOUR AESTHETIC' ?></h2>
        <a href="shop.php" class="about-btn"><?= __('abt_cta_btn') ?? 'SHOP THE COLLECTION' ?></a>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>