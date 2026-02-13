<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

<style>
    .luxury-hero {
        height: 60vh;
        background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=2070&auto=format&fit=crop');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
    }
    .luxury-title {
        font-family: 'Playfair Display', serif;
        font-size: 4rem;
        letter-spacing: 2px;
        font-style: italic;
        margin-bottom: 10px;
    }
    .luxury-subtitle {
        font-family: 'Outfit', sans-serif;
        text-transform: uppercase;
        letter-spacing: 4px;
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .story-section {
        padding: 100px 0;
        background: #fff;
    }
    .story-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 80px;
        align-items: center;
    }
    .story-content h2 {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        margin-bottom: 30px;
        color: #111;
    }
    .story-content p {
        font-family: 'Kanit', sans-serif;
        font-weight: 300;
        color: #666;
        line-height: 1.8;
        font-size: 1.05rem;
        margin-bottom: 20px;
    }
    .quote-box {
        border-left: 3px solid #1a1a1a;
        padding-left: 20px;
        margin: 30px 0;
        font-family: 'Playfair Display', serif;
        font-style: italic;
        font-size: 1.2rem;
        color: #333;
    }
    .pillar-section {
        background: #fcfcfc;
        padding: 100px 0;
        text-align: center;
    }
    .pillar-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 40px;
        margin-top: 60px;
    }
    .pillar-card {
        padding: 40px;
        /* border: 1px solid #eee; */
        background: white;
        transition: 0.5s;
    }
    .pillar-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.05);
    }
    .pillar-icon {
        font-size: 2.5rem;
        margin-bottom: 20px;
        color: #1a1a1a;
        font-family: 'Playfair Display', serif;
    }
    .pillar-title {
        font-family: 'Outfit', sans-serif;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 15px;
    }
    .pillar-desc {
        font-family: 'Kanit', sans-serif;
        font-weight: 300;
        color: #888;
        font-size: 0.9rem;
    }
    
    @media (max-width: 768px) {
        .luxury-title { font-size: 2.5rem; }
        .story-grid { grid-template-columns: 1fr; gap: 40px; }
        .pillar-grid { grid-template-columns: 1fr; }
    }
</style>

<!-- Hero -->
<div class="luxury-hero">
    <div>
        <div class="luxury-subtitle">Est. 2024</div>
        <h1 class="luxury-title">The Art of Simplistic</h1>
        <div class="luxury-subtitle">XIVEX STUDIOS</div>
    </div>
</div>

<!-- Story -->
<div class="story-section">
    <div class="container">
        <div class="story-grid">
            <div class="story-image">
                <img src="https://images.unsplash.com/photo-1558769132-cb1aea458c5e?q=80&w=1974&auto=format&fit=crop" alt="Fashion Atelier" style="width: 100%; height: 500px; object-fit: cover; filter: grayscale(20%);">
            </div>
            <div class="story-content">
                <h2>Behind the Brand</h2>
                <p>
                    XIVEX ไม่ได้เป็นเพียงแบรนด์เสื้อผ้า แต่คือตัวแทนของความหลงใหลในความสมบูรณ์แบบ เราเชื่อว่า "สไตล์" ไม่ใช่เรื่องของการวิ่งตามกระแส แต่คือการค้นพบชิ้นงานที่สะท้อนตัวตนของคุณได้อย่างชัดเจนที่สุด
                </p>
                <div class="quote-box">
                    "True luxury is not about the price tag, but the feeling of wearing something made with soul."
                </div>
                <p>
                    ทุกชิ้นงานของเราผ่านการคิดค้นและพัฒนาอย่างประณีต ตั้งแต่การทอผ้าเส้นใยพิเศษ ไปจนถึงการตัดเย็บโดยช่างฝีมือชั้นครู เพื่อให้มั่นใจว่าเสื้อผ้าที่คุณสวมใส่ จะเป็นมากกว่าเครื่องนุ่มห่ม แต่เป็นงานศิลปะที่คุณสัมผัสได้
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Pillars -->
<div class="pillar-section">
    <div class="container">
        <h2 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 10px;">Our Philosophy</h2>
        <div style="width: 50px; height: 1px; background: #000; margin: 0 auto;"></div>
        
        <div class="pillar-grid">
            <div class="pillar-card">
                <div class="pillar-icon">01.</div>
                <div class="pillar-title">Exquisite Materials</div>
                <div class="pillar-desc">
                    สัมผัสความนุ่มนวลที่แตกต่างด้วยผ้าฝ้าย Supima Cotton เกรดพรีเมียม นำเข้าจากแหล่งผลิตที่ดีที่สุด
                </div>
            </div>
            <div class="pillar-card">
                <div class="pillar-icon">02.</div>
                <div class="pillar-title">Master Craftsmanship</div>
                <div class="pillar-desc">
                    ใส่ใจในทุกรายละเอียดของฝีเข็ม การตัดเย็บที่พิถีพิถัน เพื่อทรงเสื้อที่สวยงามและคงทน
                </div>
            </div>
            <div class="pillar-card">
                <div class="pillar-icon">03.</div>
                <div class="pillar-title">Timeless Design</div>
                <div class="pillar-desc">
                    ดีไซน์ที่เรียบง่ายแต่ทรงพลัง สามารถหยิบมาใส่ได้ทุกยุคทุกสมัย ไม่ตกยุค
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
