<?php
// login.php
require_once __DIR__ . '/../services/auth_service.php';
$pageTitle = 'Giriş Yap — Gökyüzü Air';
$message = '';
$msgType = '';

if (isLoggedIn()) { header('Location: /GokyuzuAir/index.php'); exit; }

if (isset($_GET['kayit']) && $_GET['kayit'] === 'ok') {
    $message = 'Kayıt başarılı! Şimdi giriş yapabilirsiniz.';
    $msgType = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = loginUser(trim($_POST['email'] ?? ''), $_POST['sifre'] ?? '');
    $message = $result['message'];
    $msgType = $result['success'] ? 'success' : 'error';
    if ($result['success']) {
        $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']);
        header('Location: /GokyuzuAir/' . $redirect);
        exit;
    }
}

require_once __DIR__ . '/../ui/header.php';
?>
<div class="auth-wrapper">
  <div class="auth-card">
    <h2>Tekrar Hoşgeldiniz ✈</h2>
    <p>Hesabınıza giriş yaparak uçuş ayrıcalıklarından yararlanın.</p>
    <?php if ($message): ?>
      <div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
      <div class="form-full form-group-dark">
        <label>E-posta</label>
        <input type="email" name="email" placeholder="eposta@ornek.com" required>
      </div>
      <div class="form-full form-group-dark" style="margin-top:1rem;">
        <label>Şifre</label>
        <input type="password" name="sifre" placeholder="Şifrenizi girin" required>
      </div>
      <button type="submit" class="btn-continue" style="margin-top:1.5rem;">Giriş Yap →</button>
    </form>
    <div class="auth-link">Hesabınız yok mu? <a href="register.php">Kayıt Olun</a></div>
  </div>
</div>
<?php require_once __DIR__ . '/../ui/footer.php'; ?>
