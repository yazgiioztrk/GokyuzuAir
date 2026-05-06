<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gökyüzü Air — Bilet Doğrulama</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;600&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'DM Sans', sans-serif;
    background: linear-gradient(135deg, #0a1535 0%, #1a3a6e 100%);
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
  }
  .card { background: #fff; border-radius: 20px; max-width: 420px; width: 100%; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
  .card-header { background: linear-gradient(135deg, #0a1535, #1a3a6e); padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center; }
  .logo { font-family: 'Playfair Display', serif; font-size: 1.4rem; color: #fff; }
  .logo em { color: #e8a020; font-style: normal; }
  .valid-badge { background: #00b894; color: white; padding: 4px 14px; border-radius: 999px; font-size: 0.78rem; font-weight: 700; }
  .card-body { padding: 2rem; }
  .check-icon { width: 56px; height: 56px; border-radius: 50%; background: #00b894; color: white; font-size: 1.6rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
  .bilet-no-big { font-size: 1rem; font-weight: 700; color: #0a1535; letter-spacing: 2px; margin-bottom: 1.5rem; text-align:center; }
  .route-block { display: flex; align-items: center; gap: 1rem; background: #f5f7ff; border-radius: 12px; padding: 1.2rem; margin-bottom: 1.5rem; }
  .city-code { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 900; color: #0a1535; line-height: 1; }
  .city-name { font-size: 0.75rem; color: #888; margin-top: 2px; }
  .route-mid { flex: 1; text-align: center; }
  .route-line { height: 1px; background: #ddd; position: relative; margin: 8px 0; }
  .route-line::before { content: '✈'; position: absolute; top: -9px; left: 50%; transform: translateX(-50%); font-size: 0.9rem; color: #e8a020; background: #f5f7ff; padding: 0 4px; }
  .info-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 0.88rem; }
  .info-row:last-child { border-bottom: none; }
  .info-label { color: #aaa; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
  .info-val { font-weight: 600; color: #111; }
  .seat-big { display: inline-block; padding: 8px 22px; border-radius: 8px; background: #e8a020; color: #111; font-weight: 700; font-size: 1.1rem; }
  .card-footer { background: #f9f6f0; padding: 1.2rem 2rem; text-align: center; border-top: 2px dashed #e0d8c8; font-size: 0.75rem; color: #aaa; letter-spacing: 1px; }
</style>
</head>
<body>
<?php
$no     = htmlspecialchars($_GET['no'] ?? '');
$ad     = htmlspecialchars($_GET['ad'] ?? '');
$guz    = htmlspecialchars($_GET['guzergah'] ?? '');
$tarih  = htmlspecialchars($_GET['tarih'] ?? '');
$koltuk = htmlspecialchars($_GET['koltuk'] ?? '');
$ucus   = htmlspecialchars($_GET['ucus'] ?? '');
$sinif  = htmlspecialchars($_GET['sinif'] ?? '');
$firma  = htmlspecialchars($_GET['firma'] ?? '');
$parcalar = explode('->', $guz);
$kalkis_kod = strtoupper(mb_substr($parcalar[0]??'', 0, 3));
$varis_kod  = strtoupper(mb_substr($parcalar[1]??'', 0, 3));
?>
<div class="card">
  <div class="card-header">
    <div class="logo">Gökyüzü<em>Air</em></div>
    <div class="valid-badge">✓ GEÇERLİ BİLET</div>
  </div>
  <div class="card-body">
    <div class="check-icon">✓</div>
    <div class="bilet-no-big"><?= $no ?></div>
    <div class="route-block">
      <div><div class="city-code"><?= $kalkis_kod ?></div><div class="city-name"><?= $parcalar[0] ?? '' ?></div></div>
      <div class="route-mid"><div class="route-line"></div><div style="font-size:.72rem;color:#999;">Direkt</div></div>
      <div style="text-align:right"><div class="city-code"><?= $varis_kod ?></div><div class="city-name"><?= $parcalar[1] ?? '' ?></div></div>
    </div>
    <div class="info-row"><span class="info-label">Yolcu</span><span class="info-val"><?= $ad ?></span></div>
    <div class="info-row"><span class="info-label">Uçuş No</span><span class="info-val"><?= $ucus ?></span></div>
    <div class="info-row"><span class="info-label">Tarih</span><span class="info-val"><?= $tarih ?></span></div>
    <div class="info-row"><span class="info-label">Sınıf</span><span class="info-val"><?= $sinif ?></span></div>
    <div class="info-row"><span class="info-label">Havayolu</span><span class="info-val"><?= $firma ?></span></div>
    <div class="info-row"><span class="info-label">Koltuk</span><span class="info-val"><span class="seat-big"><?= $koltuk ?></span></span></div>
  </div>
  <div class="card-footer">Gökyüzü Air · Dijital Boarding Pass · Bilet geçerlidir.</div>
</div>
</body>
</html>
