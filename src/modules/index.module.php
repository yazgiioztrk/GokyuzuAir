<?php
// index.php — Ana Sayfa
require_once __DIR__ . '/../utils/cities.php';
$pageTitle = 'Gökyüzü Air — Ana Sayfa';
require_once __DIR__ . '/../ui/header.php';
$sehirler = getTurkiyeSehirleri();
?>

<section class="hero">
  <!-- Gerçek gökyüzü/bulut fotoğrafı - Unsplash ücretsiz -->
  <div class="hero-bg">
    <img src="https://images.unsplash.com/photo-1436891620584-47fd0e565afb?w=1600&q=80&auto=format&fit=crop"
         alt="Gökyüzü" class="hero-bg-img">
    <div class="hero-bg-overlay"></div>
  </div>

  <div class="hero-content">
    <div class="hero-tag">✦ Türkiye'nin En İyi Uçuşları</div>
    <h1>Hayalinizdeki Yere<br><em>Kanatlanın</em></h1>
    <p>Türkiye genelinde yüzlerce uçuş seçeneğiyle en uygun biletinizi bulun.</p>
  </div>

  <div class="search-card">
    <div class="search-tabs">
      <button class="search-tab active" onclick="setTab(this,'gidis')">✈ Gidiş</button>
      <button class="search-tab" onclick="setTab(this,'gidis-donus')">↔ Gidiş - Dönüş</button>
    </div>
    <form action="flights.php" method="GET">
      <input type="hidden" name="tip" id="tip" value="gidis">
      <div class="search-grid">
        <div class="form-group">
          <label>Nereden</label>
          <select name="kalkis" required>
            <option value="">Şehir seçin</option>
            <?php foreach ($sehirler as $sehir => $havaalani): ?>
              <option value="<?= htmlspecialchars($sehir) ?>"><?= htmlspecialchars($sehir) ?> (<?= substr($havaalani, strrpos($havaalani,'(')+1, 3) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Nereye</label>
          <select name="varis" required>
            <option value="">Şehir seçin</option>
            <?php foreach ($sehirler as $sehir => $havaalani): ?>
              <option value="<?= htmlspecialchars($sehir) ?>"><?= htmlspecialchars($sehir) ?> (<?= substr($havaalani, strrpos($havaalani,'(')+1, 3) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Gidiş Tarihi</label>
          <input type="date" name="gidis_tarihi" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
        </div>
        <div class="form-group" id="donus-field" style="display:none">
          <label>Dönüş Tarihi</label>
          <input type="date" name="donus_tarihi" min="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group">
          <label>Uçuş Sınıfı</label>
          <select name="sinif">
            <option value="Tümü">Tümü</option>
            <option value="Ekonomi">Ekonomi</option>
            <option value="Business">Business</option>
            <option value="First Class">First Class</option>
          </select>
        </div>
        <div>
          <button type="submit" class="btn-search">Uçuş Ara ✈</button>
        </div>
      </div>
    </form>
  </div>
</section>

<!-- BEYAZ BÖLÜM -->
<div class="home-white-section">
  <div class="page-wrapper">

    <!-- Özellik kartları -->
    <div class="feature-grid">
      <?php
      $cards = [
        ['✈','100+ Uçuş','Her gün güncellenen uçuş listesi'],
        ['🛡️','Güvenli Ödeme','256-bit SSL şifreli ödeme sistemi'],
        ['💺','Koltuk Seçimi','İstediğiniz koltuğu seçin'],
        ['🎫','Anında Bilet','Ödeme sonrası biletiniz hemen hazır'],
      ];
      foreach ($cards as [$icon,$title,$desc]):
      ?>
      <div class="feature-card">
        <div class="feature-icon"><?= $icon ?></div>
        <div class="feature-title"><?= $title ?></div>
        <div class="feature-desc"><?= $desc ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Popüler rotalar -->
    <div class="popular-section">
      <h2 class="section-title">🔥 Popüler Rotalar</h2>
      <div class="popular-grid">
        <?php
        $rotalar = [
          ['İstanbul','Ankara','IST','ESB','₺450\'den'],
          ['İstanbul','İzmir','IST','ADB','₺520\'den'],
          ['İstanbul','Antalya','IST','AYT','₺480\'den'],
          ['Ankara','İzmir','ESB','ADB','₺550\'den'],
          ['İzmir','İstanbul','ADB','IST','₺500\'den'],
          ['Trabzon','İstanbul','TZX','IST','₺620\'den'],
        ];
        foreach ($rotalar as [$k,$v,$kk,$vk,$fiyat]):
          $url = "flights.php?kalkis=".urlencode($k)."&varis=".urlencode($v)."&sinif=T%C3%BCm%C3%BC&gidis_tarihi=".date('Y-m-d', strtotime('+7 days'));
        ?>
        <a href="<?= $url ?>" class="popular-card">
          <div class="popular-route">
            <span class="popular-code"><?= $kk ?></span>
            <span class="popular-arrow">✈</span>
            <span class="popular-code"><?= $vk ?></span>
          </div>
          <div class="popular-cities"><?= $k ?> → <?= $v ?></div>
          <div class="popular-price"><?= $fiyat ?></div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<script>
function setTab(btn, type) {
  document.querySelectorAll('.search-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('tip').value = type;
  document.getElementById('donus-field').style.display = type === 'gidis-donus' ? 'flex' : 'none';
}
</script>
<?php require_once __DIR__ . '/../ui/footer.php'; ?>
