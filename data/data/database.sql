-- Gökyüzü Air Havayolları Veritabanı
CREATE DATABASE IF NOT EXISTS gokyuzu_db CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
USE gokyuzu_db;

CREATE TABLE IF NOT EXISTS kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(50) NOT NULL,
    soyad VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    sifre VARCHAR(255) NOT NULL,
    tel VARCHAR(20),
    cinsiyet ENUM('Erkek','Kadın','Diğer') DEFAULT 'Erkek',
    kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ucuslar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ucus_no VARCHAR(10) NOT NULL,
    kalkis_sehir VARCHAR(50) NOT NULL,
    varis_sehir VARCHAR(50) NOT NULL,
    kalkis_havaalani VARCHAR(100) NOT NULL,
    varis_havaalani VARCHAR(100) NOT NULL,
    kalkis_zamani TIME NOT NULL,
    varis_zamani TIME NOT NULL,
    sure VARCHAR(20) NOT NULL,
    sinif ENUM('Ekonomi','Business','First Class') DEFAULT 'Ekonomi',
    fiyat DECIMAL(10,2) NOT NULL,
    firma VARCHAR(50) NOT NULL,
    firma_kodu VARCHAR(10) NOT NULL,
    ucak_tipi VARCHAR(50) DEFAULT 'Boeing 737'
);

CREATE TABLE IF NOT EXISTS rezervasyonlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT,
    ucus_id INT,
    koltuk_no VARCHAR(5) NOT NULL,
    ad VARCHAR(50) NOT NULL,
    soyad VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    tel VARCHAR(20),
    cinsiyet ENUM('Erkek','Kadın','Diğer'),
    sinif VARCHAR(20),
    fiyat DECIMAL(10,2),
    gidis_tarihi DATE,
    donus_tarihi DATE,
    bilet_no VARCHAR(20) UNIQUE,
    rezervasyon_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE SET NULL,
    FOREIGN KEY (ucus_id) REFERENCES ucuslar(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS dolu_koltuklar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ucus_id INT,
    tarih DATE,
    koltuk_no VARCHAR(5),
    UNIQUE KEY unique_seat (ucus_id, tarih, koltuk_no),
    FOREIGN KEY (ucus_id) REFERENCES ucuslar(id) ON DELETE CASCADE
);

-- Uçuş verileri ekle
INSERT INTO ucuslar (ucus_no, kalkis_sehir, varis_sehir, kalkis_havaalani, varis_havaalani, kalkis_zamani, varis_zamani, sure, sinif, fiyat, firma, firma_kodu, ucak_tipi) VALUES
('TK101','İstanbul','Ankara','İstanbul Havalimanı (IST)','Esenboğa Havalimanı (ESB)','06:00','07:10','1s 10dk','Ekonomi',450.00,'Türk Hava Yolları','TK','Airbus A320'),
('TK102','İstanbul','Ankara','İstanbul Havalimanı (IST)','Esenboğa Havalimanı (ESB)','09:30','10:40','1s 10dk','Ekonomi',520.00,'Türk Hava Yolları','TK','Boeing 737'),
('PC201','İstanbul','Ankara','Sabiha Gökçen Havalimanı (SAW)','Esenboğa Havalimanı (ESB)','07:15','08:25','1s 10dk','Ekonomi',380.00,'Pegasus Airlines','PC','Airbus A320'),
('TK103','İstanbul','Ankara','İstanbul Havalimanı (IST)','Esenboğa Havalimanı (ESB)','12:00','13:10','1s 10dk','Business',1200.00,'Türk Hava Yolları','TK','Airbus A321'),
('AJ301','İstanbul','Ankara','Sabiha Gökçen Havalimanı (SAW)','Esenboğa Havalimanı (ESB)','14:30','15:40','1s 10dk','Ekonomi',360.00,'AnadoluJet','AJ','Boeing 737'),
('TK104','İstanbul','Ankara','İstanbul Havalimanı (IST)','Esenboğa Havalimanı (ESB)','18:00','19:10','1s 10dk','Ekonomi',490.00,'Türk Hava Yolları','TK','Airbus A320'),

('TK201','İstanbul','İzmir','İstanbul Havalimanı (IST)','Adnan Menderes Havalimanı (ADB)','07:00','08:10','1s 10dk','Ekonomi',520.00,'Türk Hava Yolları','TK','Boeing 737'),
('TK202','İstanbul','İzmir','İstanbul Havalimanı (IST)','Adnan Menderes Havalimanı (ADB)','10:30','11:40','1s 10dk','Ekonomi',580.00,'Türk Hava Yolları','TK','Airbus A320'),
('PC202','İstanbul','İzmir','Sabiha Gökçen Havalimanı (SAW)','Adnan Menderes Havalimanı (ADB)','08:45','09:55','1s 10dk','Ekonomi',420.00,'Pegasus Airlines','PC','Airbus A320'),
('TK203','İstanbul','İzmir','İstanbul Havalimanı (IST)','Adnan Menderes Havalimanı (ADB)','13:15','14:25','1s 10dk','Business',1350.00,'Türk Hava Yolları','TK','Airbus A321'),
('AJ302','İstanbul','İzmir','Sabiha Gökçen Havalimanı (SAW)','Adnan Menderes Havalimanı (ADB)','16:00','17:10','1s 10dk','Ekonomi',390.00,'AnadoluJet','AJ','Boeing 737'),

('TK301','İstanbul','Antalya','İstanbul Havalimanı (IST)','Antalya Havalimanı (AYT)','06:30','07:50','1s 20dk','Ekonomi',480.00,'Türk Hava Yolları','TK','Boeing 737'),
('PC203','İstanbul','Antalya','Sabiha Gökçen Havalimanı (SAW)','Antalya Havalimanı (AYT)','09:00','10:20','1s 20dk','Ekonomi',410.00,'Pegasus Airlines','PC','Airbus A320'),
('TK302','İstanbul','Antalya','İstanbul Havalimanı (IST)','Antalya Havalimanı (AYT)','12:45','14:05','1s 20dk','Business',1450.00,'Türk Hava Yolları','TK','Airbus A321'),
('AJ303','İstanbul','Antalya','Sabiha Gökçen Havalimanı (SAW)','Antalya Havalimanı (AYT)','15:30','16:50','1s 20dk','Ekonomi',370.00,'AnadoluJet','AJ','Boeing 737'),
('TK303','İstanbul','Antalya','İstanbul Havalimanı (IST)','Antalya Havalimanı (AYT)','19:00','20:20','1s 20dk','Ekonomi',510.00,'Türk Hava Yolları','TK','Boeing 737'),

('TK401','Ankara','İzmir','Esenboğa Havalimanı (ESB)','Adnan Menderes Havalimanı (ADB)','07:30','09:00','1s 30dk','Ekonomi',550.00,'Türk Hava Yolları','TK','Airbus A320'),
('PC204','Ankara','İzmir','Esenboğa Havalimanı (ESB)','Adnan Menderes Havalimanı (ADB)','11:00','12:30','1s 30dk','Ekonomi',460.00,'Pegasus Airlines','PC','Boeing 737'),
('TK402','Ankara','İzmir','Esenboğa Havalimanı (ESB)','Adnan Menderes Havalimanı (ADB)','14:00','15:30','1s 30dk','Business',1300.00,'Türk Hava Yolları','TK','Airbus A321'),
('AJ304','Ankara','İzmir','Esenboğa Havalimanı (ESB)','Adnan Menderes Havalimanı (ADB)','16:45','18:15','1s 30dk','Ekonomi',420.00,'AnadoluJet','AJ','Boeing 737'),
('TK403','Ankara','İzmir','Esenboğa Havalimanı (ESB)','Adnan Menderes Havalimanı (ADB)','19:30','21:00','1s 30dk','Ekonomi',530.00,'Türk Hava Yolları','TK','Airbus A320'),

('TK501','İzmir','İstanbul','Adnan Menderes Havalimanı (ADB)','İstanbul Havalimanı (IST)','06:00','07:10','1s 10dk','Ekonomi',500.00,'Türk Hava Yolları','TK','Boeing 737'),
('PC205','İzmir','İstanbul','Adnan Menderes Havalimanı (ADB)','Sabiha Gökçen Havalimanı (SAW)','09:30','10:40','1s 10dk','Ekonomi',430.00,'Pegasus Airlines','PC','Airbus A320'),
('TK502','İzmir','İstanbul','Adnan Menderes Havalimanı (ADB)','İstanbul Havalimanı (IST)','13:00','14:10','1s 10dk','Business',1280.00,'Türk Hava Yolları','TK','Airbus A321'),
('AJ305','İzmir','İstanbul','Adnan Menderes Havalimanı (ADB)','Sabiha Gökçen Havalimanı (SAW)','15:15','16:25','1s 10dk','Ekonomi',380.00,'AnadoluJet','AJ','Boeing 737'),
('TK503','İzmir','İstanbul','Adnan Menderes Havalimanı (ADB)','İstanbul Havalimanı (IST)','18:45','19:55','1s 10dk','Ekonomi',470.00,'Türk Hava Yolları','TK','Boeing 737');
