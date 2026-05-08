# Gökyüzü Air Havayolları — Uçak Bileti Satış Sistemi

## Proje Amacı
BGT 132 dersi Final Projesi kapsamında geliştirilen bu sistem, kullanıcıların Türkiye genelindeki şehirler arasında uçak bileti arayıp satın alabildiği tam işlevsel bir web uygulamasıdır.

## Kurulum ve Çalıştırma

### Gereksinimler
- XAMPP (Apache + PHP 7.4+ + MySQL)

### Adımlar
1. Bu klasörü (`GokyuzuAir/`) XAMPP'ın `htdocs/` dizinine kopyalayın.
2. XAMPP Denetim Masası'ndan **Apache** ve **MySQL** servislerini başlatın.
3. Tarayıcıda `http://localhost/phpmyadmin` adresine gidin.
4. `data/data/database.sql` dosyasını import edin (Yeni DB otomatik oluşturulur).
5. Tarayıcıda `http://localhost/GokyuzuAir/` adresine gidin.

## Özellikler
- Kayıt ol / Giriş yap (şifreli)
- 40+ Türkiye şehri ile uçuş arama
- Dinamik uçuş listesi (min. 5 uçuş garantili)
- Görsel koltuk haritası (dolu koltuklar kırmızı)
- Yolcu bilgileri formu
- Ödeme ekranı (kredi kartı formatlamalı)
- Dijital bilet tasarımı (yazdırılabilir)

