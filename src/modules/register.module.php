<?php
// register.php
require_once __DIR__ . '/../services/auth_service.php';
$pageTitle = 'Kayıt Ol — Gökyüzü Air';
$message = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = registerUser(
        trim($_POST['ad'] ?? ''),
        trim($_POST['soyad'] ?? ''),
        trim($_POST['email'] ?? ''),
        $_POST['sifre'] ?? '',
        trim($_POST['tel'] ?? ''),
        $_POST['cinsiyet'] ?? 'Erkek'
    );
    $message = $result['message'];
    $msgType = $result['success'] ? 'success' : 'error';
    if ($result['success']) {
        header('Location: /GokyuzuAir/login.php?kayit=ok');
        exit;
    }
}

require_once __DIR__ . '/../ui/header.php';
?>
<div class="auth-wrapper">
  <div class="auth-card">
    <h2>Hesap Oluştur ✦</h2>
    <p>Gökyüzü Air'e katılın, ayrıcalıklı uçuş deneyimini yaşayın.</p>
    <?php if ($message): ?>
      <div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
      <div class="form-row">
        <div class="form-group-dark">
          <label>Ad</label>
          <input type="text" name="ad" placeholder="Adınız" required value="<?= htmlspecialchars($_POST['ad'] ?? '') ?>">
        </div>
        <div class="form-group-dark">
          <label>Soyad</label>
          <input type="text" name="soyad" placeholder="Soyadınız" required value="<?= htmlspecialchars($_POST['soyad'] ?? '') ?>">
        </div>
      </div>
      <div class="form-full form-group-dark">
        <label>E-posta</label>
        <input type="email" name="email" placeholder="eposta@ornek.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-row">
        <div class="form-group-dark">
          <label>Telefon</label>
          <input type="tel" name="tel" placeholder="05XX XXX XX XX" value="<?= htmlspecialchars($_POST['tel'] ?? '') ?>">
        </div>
        <div class="form-group-dark">
          <label>Cinsiyet</label>
          <select name="cinsiyet">
            <option value="Erkek">Erkek</option>
            <option value="Kadın">Kadın</option>
            <option value="Diğer">Diğer</option>
          </select>
        </div>
      </div>
      <div class="form-full form-group-dark">
        <label>Şifre</label>
        <input type="password" name="sifre" placeholder="En az 6 karakter" required minlength="6">
      </div>
      <button type="submit" class="btn-continue">Kayıt Ol →</button>
    </form>
    <div class="auth-link">Zaten hesabınız var mı? <a href="login.php">Giriş Yapın</a></div>
  </div>
</div>
<?php require_once __DIR__ . '/../ui/footer.php'; ?>
