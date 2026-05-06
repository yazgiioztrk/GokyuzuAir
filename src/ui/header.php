<?php
// src/ui/header.php
require_once __DIR__ . '/../core/session.php';
$user = getUser();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'Gökyüzü Air' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/GokyuzuAir/assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="logo">
    <span class="logo-icon">✈</span>
    <span class="logo-text">Gökyüzü<em>Air</em></span>
  </a>
  <div class="nav-actions">
    <?php if ($user): ?>
      <div class="user-badge">
        <span class="user-avatar"><?= mb_strtoupper(mb_substr($user['ad'], 0, 1)) ?></span>
        <span class="user-name"><?= htmlspecialchars($user['ad'] . ' ' . $user['soyad']) ?></span>
      </div>
      <a href="logout.php" class="btn-nav btn-outline">Çıkış</a>
    <?php else: ?>
      <a href="login.php" class="btn-nav btn-outline">Giriş Yap</a>
      <a href="register.php" class="btn-nav btn-primary">Kayıt Ol</a>
    <?php endif; ?>
  </div>
</nav>
