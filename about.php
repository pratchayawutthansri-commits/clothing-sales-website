<?php
require_once 'includes/init.php';
require_once 'includes/header.php';

$heroImg  = "https://images.unsplash.com/photo-1523381210434-271e8be1f52b?q=80&w=2000&auto=format&fit=crop";
$storyImg = "https://images.unsplash.com/photo-1556905055-8f358a7a47b2?q=80&w=2000&auto=format&fit=crop";
?>

<!-- About Page — Luxury Editorial -->
<style>
    /* ── ต่อจาก style.css เดิม prefix .ab- ทั้งหมด ── */
    @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;1,300;1,400&display=swap');

    body { background: #faf9f7; }

    /* HERO */
    .ab-hero {
        height: 100vh;
        display: grid;
        grid-template-columns: 1fr 1fr;
        overflow: hidden;
    }
    .ab-hero-left {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 80px 72px;
        background: #faf9f7;
    }
    .ab-eyebrow {
        font-family: var(--font-mono);
        font-size: 0.65rem;
        letter-spacing: 0.3em;
        text-transform: uppercase;
        color: var(--accent-color);
        margin-bottom: 28px;
        opacity: 0;
        animation: slideUp 0.7s ease forwards 0.2s;
    }
    .ab-hero-left h1 {
        font-family: 'Cormorant Garamond', serif !important;
        font-size: clamp(3.2rem, 5.5vw, 5.5rem) !important;
        font-weight: 300 !important;
        font-style: italic !important;
        line-height: 1.08 !important;
        letter-spacing: -0.02em !important;
        color: var(--text-color) !important;
        text-transform: none !important;
        margin-bottom: 36px;
        opacity: 0;
        animation: slideUp 0.8s ease forwards 0.35s;
    }
    .ab-hero-left p {
        font-size: 0.95rem;
        font-weight: 300;
        color: var(--accent-color);
        line-height: 1.85;
        max-width: 360px;
        opacity: 0;
        animation: slideUp 0.8s ease forwards 0.5s;
    }
    .ab-hero-right { overflow: hidden; }
    .ab-hero-right img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
        filter: saturate(0.8);
        transition: transform 8s ease;
    }
    .ab-hero:hover .ab-hero-right img { transform: scale(1.04); }

    /* STORY */
    .ab-story {
        padding: 160px 72px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 120px;
        align-items: center;
        border-top: 1px solid #ede9e1;
        background: #faf9f7;
    }
    .ab-label {
        font-family: var(--font-mono);
        font-size: 0.65rem;
        letter-spacing: 0.3em;
        text-transform: uppercase;
        color: var(--accent-color);
        margin-bottom: 32px;
    }
    .ab-story h2 {
        font-family: 'Cormorant Garamond', serif !important;
        font-size: clamp(2.2rem, 4vw, 3.8rem) !important;
        font-weight: 300 !important;
        font-style: italic !important;
        line-height: 1.15 !important;
        letter-spacing: -0.01em !important;
        text-transform: none !important;
        color: var(--text-color) !important;
        margin-bottom: 40px;
    }
    .ab-story p {
        font-size: 0.95rem;
        font-weight: 300;
        color: #666;
        line-height: 1.95;
        margin-bottom: 20px;
    }
    .ab-img-wrap { overflow: hidden; }
    .ab-img-wrap img {
        width: 100%;
        aspect-ratio: 4/5;
        object-fit: cover;
        filter: saturate(0.8) contrast(1.05);
        transition: transform 0.8s ease;
    }
    .ab-img-wrap:hover img { transform: scale(1.03); }

    /* VALUES */
    .ab-values {
        padding: 160px 72px;
        background: #faf9f7;
        border-top: 1px solid #ede9e1;
    }
    .ab-values-top {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 80px;
        align-items: end;
        margin-bottom: 100px;
    }
    .ab-values-top h2 {
        font-family: 'Cormorant Garamond', serif !important;
        font-size: clamp(2.5rem, 5vw, 5rem) !important;
        font-weight: 300 !important;
        font-style: italic !important;
        line-height: 1.05 !important;
        text-transform: none !important;
        color: var(--text-color) !important;
    }
    .ab-values-top p {
        font-size: 0.95rem;
        font-weight: 300;
        color: #888;
        line-height: 1.9;
    }
    .ab-values-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        border-top: 1px solid #ede9e1;
    }
    .ab-val {
        padding: 60px 48px 60px 0;
        border-right: 1px solid #ede9e1;
        transition: opacity 0.3s ease;
    }
    .ab-val:nth-child(2) { padding-left: 48px; }
    .ab-val:last-child   { border-right: none; padding-right: 0; padding-left: 48px; }
    .ab-values-list:hover .ab-val       { opacity: 0.35; }
    .ab-values-list:hover .ab-val:hover { opacity: 1; }
    .ab-val-num {
        font-family: var(--font-mono);
        font-size: 0.65rem;
        letter-spacing: 0.2em;
        color: #ccc;
        margin-bottom: 32px;
    }
    .ab-val h3 {
        font-family: 'Cormorant Garamond', serif !important;
        font-size: 1.6rem !important;
        font-weight: 400 !important;
        font-style: italic !important;
        text-transform: none !important;
        color: var(--text-color) !important;
        margin-bottom: 16px;
    }
    .ab-val p {
        font-size: 0.85rem;
        font-weight: 300;
        color: #888;
        line-height: 1.85;
    }

    /* MISSION */
    .ab-mission {
        padding: 180px 72px;
        background: #faf9f7;
        border-top: 1px solid #ede9e1;
        text-align: center;
    }
    .ab-mission blockquote {
        font-family: 'Cormorant Garamond', serif;
        font-size: clamp(2rem, 5vw, 4.5rem);
        font-weight: 300;
        font-style: italic;
        line-height: 1.2;
        letter-spacing: -0.01em;
        color: var(--text-color);
        max-width: 900px;
        margin: 0 auto 64px;
    }
    .ab-cta-link {
        font-family: var(--font-mono);
        font-size: 0.7rem;
        letter-spacing: 0.25em;
        text-transform: uppercase;
        color: var(--text-color);
        border-bottom: 1px solid var(--text-color);
        padding-bottom: 4px;
        transition: opacity 0.3s ease;
    }
    .ab-cta-link:hover { opacity: 0.4; }

    /* SCROLL REVEAL */
    .ab-r {
        opacity: 0;
        transform: translateY(40px);
        transition: opacity 0.9s cubic-bezier(0.16,1,0.3,1),
                    transform 0.9s cubic-bezier(0.16,1,0.3,1);
    }
    .ab-r.on { opacity: 1; transform: none; }
    .ab-d1 { transition-delay: 0.1s; }
    .ab-d2 { transition-delay: 0.2s; }
    .ab-d3 { transition-delay: 0.3s; }

    /* RESPONSIVE */
    @media (max-width: 900px) {
        .ab-hero        { grid-template-columns: 1fr; height: auto; }
        .ab-hero-right  { height: 65vw; }
        .ab-hero-left   { padding: 60px 30px; }
        .ab-story       { grid-template-columns: 1fr; gap: 60px; padding: 100px 30px; }
        .ab-values      { padding: 100px 30px; }
        .ab-values-top  { grid-template-columns: 1fr; gap: 32px; margin-bottom: 60px; }
        .ab-values-list { grid-template-columns: 1fr; }
        .ab-val,
        .ab-val:nth-child(2),
        .ab-val:last-child {
            padding: 48px 0;
            border-right: none;
            border-bottom: 1px solid #ede9e1;
        }
        .ab-val:last-child { border-bottom: none; }
        .ab-mission { padding: 100px 30px; }
    }
</style>

<div class="about-page">

    <!-- ── HERO ── -->
    <section class="ab-hero">
        <div class="ab-hero-left">
            <p class="ab-eyebrow">2025 — BANGKOK</p>
            <h1><?= __('abt_hero_h1') ?></h1>
            <p><?= __('abt_hero_p') ?></p>
        </div>
        <div class="ab-hero-right">
            <img src="<?= $heroImg ?>" alt="XIVEX">
        </div>
    </section>
    
    <!-- ── STORY ── -->
    <section class="ab-story">
        <div class="ab-r">
            <p class="ab-label"><?= __('abt_vision_label') ?? 'เรื่องราวของเรา' ?></p>
            <h2><?= __('abt_vision_h2') ?></h2>
            <p><?= __('abt_vision_p') ?></p>
            <p><?= __('abt_craft_p') ?></p>
        </div>
        <div class="ab-img-wrap ab-r ab-d1">
            <img src="<?= $storyImg ?>" alt="<?= __('abt_vision_h2') ?>" loading="lazy">
        </div>
    </section>

    <!-- ── VALUES ── -->
    <section class="ab-values">
        <div class="ab-values-top ab-r">
            <h2><?= __('abt_values_h2') ?></h2>
            <p><?= __('abt_values_sub') ?? 'ทุกการตัดสินใจใน XIVEX วัดจากหลักการเดียวกัน — ถ้าไม่ดีพอสำหรับเรา มันก็ไม่ดีพอสำหรับคุณ' ?></p>
        </div>
        <div class="ab-values-list">
            <div class="ab-val ab-r ab-d1">
                <p class="ab-val-num">01</p>
                <h3><?= __('abt_val_1_title') ?></h3>
                <p><?= __('abt_val_1_desc') ?></p>
            </div>
            <div class="ab-val ab-r ab-d2">
                <p class="ab-val-num">02</p>
                <h3><?= __('abt_val_2_title') ?></h3>
                <p><?= __('abt_val_2_desc') ?></p>
            </div>
            <div class="ab-val ab-r ab-d3">
                <p class="ab-val-num">03</p>
                <h3><?= __('abt_val_3_title') ?></h3>
                <p><?= __('abt_val_3_desc') ?></p>
            </div>
        </div>
    </section>

    <!-- ── MISSION / CTA ── -->
    <section class="ab-mission ab-r">
        <blockquote><?= __('abt_cta_h2') ?></blockquote>
        <a href="shop.php" class="ab-cta-link"><?= __('abt_cta_btn') ?></a>
    </section>

</div>

<script>
    const io = new IntersectionObserver(
        entries => entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('on'); }),
        { threshold: 0.12 }
    );
    document.querySelectorAll('.ab-r').forEach(el => io.observe(el));
</script>

<?php require_once 'includes/footer.php'; ?>