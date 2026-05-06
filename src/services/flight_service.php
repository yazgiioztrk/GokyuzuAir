<?php
// src/services/flight_service.php
require_once __DIR__ . '/../data/db.php';

function searchFlights($kalkis, $varis, $sinif) {
    $db = getDB();
    $kalkis = $db->real_escape_string($kalkis);
    $varis  = $db->real_escape_string($varis);
    $sinif  = $db->real_escape_string($sinif);
    $q = "SELECT * FROM ucuslar WHERE kalkis_sehir='$kalkis' AND varis_sehir='$varis'";
    if ($sinif && $sinif !== 'Tümü') {
        $q .= " AND sinif='$sinif'";
    }
    $q .= " ORDER BY kalkis_zamani ASC";
    $result = $db->query($q);
    $flights = [];
    while ($row = $result->fetch_assoc()) {
        $flights[] = $row;
    }
    return $flights;
}

function getFlightById($id) {
    $db = getDB();
    $id = (int)$id;
    $result = $db->query("SELECT * FROM ucuslar WHERE id=$id");
    return $result->fetch_assoc();
}

function getDoluKoltuklar($ucus_id, $tarih) {
    $db = getDB();
    $ucus_id = (int)$ucus_id;
    $tarih = $db->real_escape_string($tarih);
    $result = $db->query("SELECT koltuk_no FROM dolu_koltuklar WHERE ucus_id=$ucus_id AND tarih='$tarih'");
    $dolu = [];
    while ($row = $result->fetch_assoc()) {
        $dolu[] = $row['koltuk_no'];
    }
    return $dolu;
}

function saveRezervasyon($data) {
    $db = getDB();
    $bilet_no = 'GAR' . strtoupper(substr(md5(uniqid()), 0, 8));

    // kullanici_id FK kontrolu
    $kullanici_id = "NULL";
    if (!empty($data["kullanici_id"])) {
        $kid = (int)$data["kullanici_id"];
        $kcheck = $db->query("SELECT id FROM kullanicilar WHERE id=$kid");
        if ($kcheck && $kcheck->num_rows > 0) {
            $kullanici_id = $kid;
        }
    }
    $koltuk_no  = $db->real_escape_string($data['koltuk_no']);
    $ad         = $db->real_escape_string($data['ad']);
    $soyad      = $db->real_escape_string($data['soyad']);
    $email      = $db->real_escape_string($data['email']);
    $tel        = $db->real_escape_string($data['tel']);
    $cinsiyet   = $db->real_escape_string($data['cinsiyet']);
    $sinif      = $db->real_escape_string($data['sinif']);
    $fiyat      = (float)$data['fiyat'];
    $gidis      = $db->real_escape_string($data['gidis_tarihi']);
    $donus      = $db->real_escape_string($data['donus_tarihi'] ?? '');

    // Dinamik uçuş ise (ID >= 1000) önce ucuslar tablosuna ekle
    $ucus_id = (int)$data['ucus_id'];
    if ($ucus_id >= 1000 && !empty($data['ucus_json'])) {
        $u = is_array($data['ucus_json']) ? $data['ucus_json'] : json_decode($data['ucus_json'], true);
        if ($u) {
            $ucus_no   = $db->real_escape_string($u['ucus_no'] ?? 'DYN001');
            $k_sehir   = $db->real_escape_string($u['kalkis_sehir'] ?? '');
            $v_sehir   = $db->real_escape_string($u['varis_sehir'] ?? '');
            $k_hava    = $db->real_escape_string($u['kalkis_havaalani'] ?? '');
            $v_hava    = $db->real_escape_string($u['varis_havaalani'] ?? '');
            $k_zaman   = $db->real_escape_string(substr($u['kalkis_zamani'] ?? '00:00', 0, 5));
            $v_zaman   = $db->real_escape_string(substr($u['varis_zamani'] ?? '00:00', 0, 5));
            $sure      = $db->real_escape_string($u['sure'] ?? '1s 30dk');
            $u_sinif   = $db->real_escape_string($u['sinif'] ?? 'Ekonomi');
            $u_fiyat   = (float)($u['fiyat'] ?? $fiyat);
            $firma     = $db->real_escape_string($u['firma'] ?? 'Gökyüzü Air');
            $firma_kod = $db->real_escape_string($u['firma_kodu'] ?? 'SW');
            $ucak      = $db->real_escape_string($u['ucak_tipi'] ?? 'Boeing 737');

            $db->query("INSERT INTO ucuslar 
                (ucus_no,kalkis_sehir,varis_sehir,kalkis_havaalani,varis_havaalani,
                 kalkis_zamani,varis_zamani,sure,sinif,fiyat,firma,firma_kodu,ucak_tipi)
                VALUES 
                ('$ucus_no','$k_sehir','$v_sehir','$k_hava','$v_hava',
                 '$k_zaman','$v_zaman','$sure','$u_sinif',$u_fiyat,'$firma','$firma_kod','$ucak')");
            $ucus_id = (int)$db->insert_id;
        }
    }

    // ucus_id hala geçersizse NULL olarak kaydet (FK sorununu önlemek için)
    $check = $db->query("SELECT id FROM ucuslar WHERE id=$ucus_id");
    if (!$check || $check->num_rows === 0) {
        $ucus_id_sql = 'NULL';
    } else {
        $ucus_id_sql = $ucus_id;
    }

    $db->query("INSERT INTO rezervasyonlar 
        (kullanici_id,ucus_id,koltuk_no,ad,soyad,email,tel,cinsiyet,sinif,fiyat,gidis_tarihi,donus_tarihi,bilet_no)
        VALUES 
        ($kullanici_id,$ucus_id_sql,'$koltuk_no','$ad','$soyad','$email','$tel','$cinsiyet','$sinif',$fiyat,'$gidis','$donus','$bilet_no')");

    if ($ucus_id_sql !== 'NULL') {
        $db->query("INSERT IGNORE INTO dolu_koltuklar (ucus_id,tarih,koltuk_no) VALUES ($ucus_id,'$gidis','$koltuk_no')");
    }

    return $bilet_no;
}
?>
