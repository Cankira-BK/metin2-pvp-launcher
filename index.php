<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Güçlü Otomotiv - Güvenilir İkinci El Araç Alım Satım</title>

    <!-- Ayrı CSS -->
    <link rel="stylesheet" href="assets/styles.css" />

    <!-- Ayrı JS -->
    <script defer src="assets/app.js"></script>
</head>
<body>
    <header>
        <nav>
            <div class="logo">🚗 GÜÇLÜ OTOMOTİV</div>
            <ul class="nav-links">
                <li><a href="#anasayfa">Ana Sayfa</a></li>
                <li><a href="#araclar">Araçlar</a></li>
                <li><a href="#hizmetler">Hizmetler</a></li>
                <li><a href="#yorumlar">Yorumlar</a></li>
                <li><a href="#iletisim">İletişim</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero" id="anasayfa">
        <div class="hero-content">
            <h1>Güvenle Alın, Huzurla Sürün</h1>
            <p>20 Yıllık Tecrübe ile İkinci El Araç Alım Satım</p>
            <a href="#araclar" class="btn">Araçları İncele</a>
        </div>
    </section>

    <section class="trust-badges">
        <div class="container">
            <h2 class="section-title">Neden Güçlü Otomotiv?</h2>
            <div class="badges-grid">
                <div class="badge-card">
                    <div class="badge-icon">✓</div>
                    <h3>2000+ Mutlu Müşteri</h3>
                    <p>20 yıldır binlerce müşterimize güvenilir hizmet sunuyoruz</p>
                </div>
                <div class="badge-card">
                    <div class="badge-icon">🔍</div>
                    <h3>Ekspertiz Garantisi</h3>
                    <p>Tüm araçlarımız detaylı ekspertiz kontrolünden geçer</p>
                </div>
                <div class="badge-card">
                    <div class="badge-icon">🤝</div>
                    <h3>Takas İmkanı</h3>
                    <p>Aracınızı değerinde değerlendirip takas yapabilirsiniz</p>
                </div>
                <div class="badge-card">
                    <div class="badge-icon">📋</div>
                    <h3>Tüm İşlemler</h3>
                    <p>Ruhsat, noter, sigorta işlemlerinizi biz hallederiz</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="araclar">
        <div class="container">
            <h2 class="section-title">Vitrinimizden Seçmeler</h2>
            <div class="vehicles-grid">
                <!-- app.js burayı dolduracak -->
            </div>
            <div style="text-align: center; margin-top: 3rem;">
                <a href="https://gucluotomotivosmaniye.sahibinden.com/" target="_blank" class="btn">Tüm Araçları Görüntüle</a>
            </div>
        </div>
    </section>

    <section class="section services" id="hizmetler">
        <div class="container">
            <h2 class="section-title">Hizmetlerimiz</h2>
            <div class="services-grid">
                <div class="service-card">
                    <h3>🚗 Araç Alım-Satım</h3>
                    <p>Geniş araç yelpazemizden size en uygun aracı bulun. Tüm araçlarımız detaylı kontrolden geçer.</p>
                </div>
                <div class="service-card">
                    <h3>🔄 Takas</h3>
                    <p>Mevcut aracınızı en iyi fiyattan değerlendirip, yeni aracınıza takas edebilirsiniz.</p>
                </div>
                <div class="service-card">
                    <h3>🔍 Ekspertiz</h3>
                    <p>Satın almak istediğiniz aracın detaylı ekspertiz raporu ile güvenle alın.</p>
                </div>
                <div class="service-card">
                    <h3>💰 Kredi Desteği</h3>
                    <p>Anlaşmalı bankalarımız ile uygun faiz oranlarında araç kredisi imkanı.</p>
                </div>
                <div class="service-card">
                    <h3>📋 İşlem Kolaylığı</h3>
                    <p>Ruhsat, noter, sigorta ve trafik tescil işlemlerinizi sizin için en kolay yolla hallederiz.</p>
                </div>
                <div class="service-card">
                    <h3>💼 Danışmanlık</h3>
                    <p>20 yıllık tecrübemizle araç alım-satım sürecinde size rehberlik ederiz.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section testimonials" id="yorumlar">
        <div class="container">
            <h2 class="section-title" style="color: white;">Müşterilerimiz Ne Diyor?</h2>
            <div class="testimonial-card">
                <p>"3 yıldır aldığım araçla hiçbir sorun yaşamadım. Güçlü Otomotiv'in güvenilirliği ve samimiyeti gerçekten takdire şayan. Herkese tavsiye ederim."</p>
                <div class="testimonial-author">- Mehmet Yılmaz, İstanbul</div>
            </div>
            <div class="testimonial-card">
                <p>"Araç alırken çok detaylı bilgi verdiler. Tüm işlemleri takip ettiler. Aracımı takas ederken de çok adil davrandılar. Teşekkürler Güçlü Otomotiv."</p>
                <div class="testimonial-author">- Ayşe Kaya, Ankara</div>
            </div>
            <div class="testimonial-card">
                <p>"İlk araç alımımdı ve çok tedirginidim. Ama Güçlü Otomotiv ekibi her konuda yardımcı oldu. Ekspertiz raporu sayesinde gönül rahatlığıyla aldım."</p>
                <div class="testimonial-author">- Can Özdemir, İzmir</div>
            </div>
        </div>
    </section>

    <section class="section contact" id="iletisim">
        <div class="container">
            <h2 class="section-title">İletişim</h2>
        <div class="contact-grid">
            <div class="contact-info">
                <h3>Bize Ulaşın</h3>
                <div class="contact-item">
                    <span>📍</span>
                    <span>Atatürk Cad. No: 123, Merkez / Osmaniye</span>
                </div>
                <div class="contact-item">
                    <span>📞</span>
                    <span>0328 123 45 67</span>
                </div>
                <div class="contact-item">
                    <span>📱</span>
                    <span>0532 123 45 67</span>
                </div>
                <div class="contact-item">
                    <span>✉️</span>
                    <span>info@gucluotomotiv.com</span>
                </div>
                <div style="margin-top: 2rem;">
                    <a href="https://wa.me/905321234567" class="whatsapp-btn" target="_blank">
                        💬 WhatsApp ile İletişime Geç
                    </a>
                </div>
            </div>
            <div class="contact-info">
                <h3>Çalışma Saatlerimiz</h3>
                <div class="contact-item">
                    <span>🕐</span>
                    <span>Pazartesi - Cumartesi: 09:00 - 19:00</span>
                </div>
                <div class="contact-item">
                    <span>🕐</span>
                    <span>Pazar: 10:00 - 17:00</span>
                </div>
                <div style="margin-top: 2rem;">
                    <h3>Bizi Takip Edin</h3>
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <a href="#" style="color: #16213e; font-size: 2rem;">📘</a>
                        <a href="#" style="color: #16213e; font-size: 2rem;">📸</a>
                        <a href="#" style="color: #16213e; font-size: 2rem;">🎥</a>
                    </div>
                </div>
            </div>
            <div class="contact-info">
                <h3>Sahibinden Profilimiz</h3>
                <p>Tüm araçlarımız ve detaylı bilgileri için Sahibinden.com profilimizi ziyaret edebilirsiniz.</p>
                <a href="https://gucluotomotivosmaniye.sahibinden.com/" target="_blank" class="btn" style="margin-top: 1rem; display: inline-block;">
                    Sahibinden Profilini Görüntüle
                </a>
            </div>
        </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; Güçlü Otomotiv. Tüm hakları saklıdır.</p>
            <p>Güvenilir ikinci el araç alım satım platformunuz</p>
        </div>
    </footer>
</body>
</html>
