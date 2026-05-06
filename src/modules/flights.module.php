<?php
// flights.php — Uçuş Listesi
require_once __DIR__ . '/../services/flight_service.php';
require_once __DIR__ . '/../utils/cities.php';
$pageTitle = 'Uçuşlar — Gökyüzü Air';

$kalkis       = trim($_GET['kalkis'] ?? '');
$varis        = trim($_GET['varis'] ?? '');
$sinif        = trim($_GET['sinif'] ?? 'Tümü');
$gidis_tarihi = trim($_GET['gidis_tarihi'] ?? date('Y-m-d'));
$donus_tarihi = trim($_GET['donus_tarihi'] ?? '');
$tip          = trim($_GET['tip'] ?? 'gidis');
$siralama     = trim($_GET['siralama'] ?? 'fiyat');

$ucuslar = [];
if ($kalkis && $varis) {
    $ucuslar = searchFlights($kalkis, $varis, $sinif);
    if (empty($ucuslar)) {
        $sehirler = getTurkiyeSehirleri();
        $firmalar = [
            ['Türk Hava Yolları','TK','Boeing 737'],
            ['Pegasus Airlines','PC','Airbus A320'],
            ['AnadoluJet','AJ','Boeing 737'],
            ['SunExpress','XQ','Boeing 737'],
            ['Corendon Air','CAI','Airbus A320'],
            ['Gökyüzü Air','GA','Airbus A321'],
        ];
        $saatler = ['06:15','08:30','10:45','13:00','16:20','19:10'];
        srand(crc32($kalkis.$varis.$gidis_tarihi));
        for ($i = 0; $i < 6; $i++) {
            $f = $firmalar[$i];
            $dk = rand(55, 140);
            $kalkis_ts = strtotime($saatler[$i]);
            $varis_saat = date('H:i', $kalkis_ts + $dk * 60);
            $taban = rand(350, 900);
            $fiyat = $sinif === 'Business' ? $taban*2.5 : ($sinif === 'First Class' ? $taban*4 : $taban);
            $ucus_sinif = ($sinif && $sinif !== 'Tümü') ? $sinif : 'Ekonomi';
            $ucuslar[] = [
                'id'               => 1000 + $i,
                'ucus_no'          => $f[1].rand(100,999),
                'kalkis_sehir'     => $kalkis,
                'varis_sehir'      => $varis,
                'kalkis_havaalani' => $sehirler[$kalkis] ?? "$kalkis Havalimanı",
                'varis_havaalani'  => $sehirler[$varis] ?? "$varis Havalimanı",
                'kalkis_zamani'    => $saatler[$i],
                'varis_zamani'     => $varis_saat,
                'sure'             => floor($dk/60).'s '.($dk%60).'dk',
                'sure_dk'          => $dk,
                'sinif'            => $ucus_sinif,
                'fiyat'            => round($fiyat),
                'firma'            => $f[0],
                'firma_kodu'       => $f[1],
                'ucak_tipi'        => $f[2],
            ];
        }
    } else {
        foreach ($ucuslar as &$u) {
            if (!isset($u['sure_dk'])) {
                preg_match('/(\d+)s\s*(\d*)/', $u['sure'], $m);
                $u['sure_dk'] = (int)($m[1]??0)*60 + (int)($m[2]??0);
            }
        }
        unset($u);
    }
}

// Sıralama
usort($ucuslar, function($a, $b) use ($siralama) {
    switch ($siralama) {
        case 'fiyat':   return $a['fiyat'] <=> $b['fiyat'];
        case 'sure':    return ($a['sure_dk']??999) <=> ($b['sure_dk']??999);
        case 'kalkis':  return strcmp($a['kalkis_zamani'], $b['kalkis_zamani']);
        case 'varis':   return strcmp($a['varis_zamani'], $b['varis_zamani']);
        default:        return 0;
    }
});

$min_fiyat = !empty($ucuslar) ? min(array_column($ucuslar, 'fiyat')) : 0;
$min_sure  = !empty($ucuslar) ? min(array_map(fn($u) => $u['sure_dk']??999, $ucuslar)) : 0;

require_once __DIR__ . '/../ui/header.php';
?>

<div class="flights-page">
<div class="page-wrapper">
  <a href="index.php" class="btn-back">← Aramaya Dön</a>

  <div class="steps">
    <div class="step-item"><div class="step-dot active">1</div><span class="step-label">Uçuş Seç</span></div>
    <div class="step-line"></div>
    <div class="step-item"><div class="step-dot">2</div><span class="step-label">Koltuk</span></div>
    <div class="step-line"></div>
    <div class="step-item"><div class="step-dot">3</div><span class="step-label">Yolcu</span></div>
    <div class="step-line"></div>
    <div class="step-item"><div class="step-dot">4</div><span class="step-label">Ödeme</span></div>
    <div class="step-line"></div>
    <div class="step-item"><div class="step-dot">5</div><span class="step-label">Bilet</span></div>
  </div>

  <h1 class="page-title"><?= htmlspecialchars($kalkis) ?> → <?= htmlspecialchars($varis) ?></h1>
  <p class="page-subtitle">
    <?= date('d F Y', strtotime($gidis_tarihi)) ?>
    <?= $donus_tarihi ? ' · Dönüş: '.date('d F Y', strtotime($donus_tarihi)) : '' ?>
    · <?= htmlspecialchars($sinif) ?> · <strong><?= count($ucuslar) ?> uçuş bulundu</strong>
  </p>

  <?php if (!empty($ucuslar)): ?>

  <!-- FİLTRE ÇUBUĞU -->
  <div class="filter-bar">
    <span class="filter-label">Sırala:</span>
    <?php
    $filters = [
      'fiyat'  => ['💰','En Uygun'],
      'sure'   => ['⚡','En Hızlı'],
      'kalkis' => ['🛫','Erken Kalkış'],
      'varis'  => ['🛬','Erken Varış'],
    ];
    foreach ($filters as $key => [$icon, $label]):
      $active = $siralama === $key ? 'active' : '';
      $url = '?'.http_build_query(array_merge($_GET, ['siralama'=>$key]));
    ?>
      <a href="<?= $url ?>" class="filter-btn <?= $active ?>">
        <?= $icon ?> <?= $label ?>
        <?php if ($key==='fiyat' && $siralama==='fiyat'): ?>
          <em class="filter-from">₺<?= number_format($min_fiyat,0,'.','.') ?>'den</em>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- UÇUŞ LİSTESİ -->
  <div class="flight-list">
    <?php foreach ($ucuslar as $i => $ucus):
      $is_cheapest = ($ucus['fiyat'] == $min_fiyat);
      $is_fastest  = (($ucus['sure_dk']??999) == $min_sure);
    ?>
      <form action="seats.php" method="GET">
        <input type="hidden" name="ucus_id"      value="<?= $ucus['id'] ?>">
        <input type="hidden" name="gidis_tarihi"  value="<?= htmlspecialchars($gidis_tarihi) ?>">
        <input type="hidden" name="donus_tarihi"  value="<?= htmlspecialchars($donus_tarihi) ?>">
        <input type="hidden" name="kalkis"        value="<?= htmlspecialchars($kalkis) ?>">
        <input type="hidden" name="varis"         value="<?= htmlspecialchars($varis) ?>">
        <input type="hidden" name="ucus_json"     value="<?= htmlspecialchars(json_encode($ucus)) ?>">

        <div class="flight-card <?= ($is_cheapest && $siralama==='fiyat') ? 'flight-card-featured' : '' ?>" onclick="this.closest('form').submit()">
          <div class="flight-badges">
            <?php if ($is_cheapest): ?><span class="badge badge-cheap">💰 En Uygun</span><?php endif; ?>
            <?php if ($is_fastest):  ?><span class="badge badge-fast">⚡ En Hızlı</span><?php endif; ?>
          </div>

          <div class="flight-airline">
            <div class="airline-code"><?= htmlspecialchars($ucus['firma_kodu']) ?></div>
            <div class="airline-name"><?= htmlspecialchars($ucus['firma']) ?></div>
          </div>

          <div class="flight-route">
            <div class="flight-time-block">
              <div class="flight-time"><?= substr($ucus['kalkis_zamani'],0,5) ?></div>
              <div class="flight-city"><?= htmlspecialchars($ucus['kalkis_sehir']) ?></div>
            </div>
            <div class="flight-duration">
              <div class="duration-line"></div>
              <div class="duration-text"><?= htmlspecialchars($ucus['sure']) ?> · <?= htmlspecialchars($ucus['ucak_tipi']) ?></div>
            </div>
            <div class="flight-time-block">
              <div class="flight-time"><?= substr($ucus['varis_zamani'],0,5) ?></div>
              <div class="flight-city"><?= htmlspecialchars($ucus['varis_sehir']) ?></div>
            </div>
          </div>

          <div>
            <div class="flight-class-badge"><?= htmlspecialchars($ucus['sinif']) ?></div>
            <div class="flight-no"><?= htmlspecialchars($ucus['ucus_no']) ?></div>
          </div>

          <div class="flight-price">
            <div class="price-amount">₺<?= number_format($ucus['fiyat'],0,'.','.') ?></div>
            <div class="price-label">kişi başı</div>
          </div>

          <button type="submit" class="select-btn" onclick="event.stopPropagation()">Seç →</button>
        </div>
      </form>
    <?php endforeach; ?>
  </div>

  <?php else: ?>
    <div class="no-results">
      <div class="icon">✈</div>
      <p>Seçilen kriterlere uygun uçuş bulunamadı.</p>
      <a href="index.php" class="btn-back" style="display:inline-flex;margin-top:1rem;">← Yeni Arama Yap</a>
    </div>
  <?php endif; ?>
</div>
</div>

<?php require_once __DIR__ . '/../ui/footer.php'; ?>
