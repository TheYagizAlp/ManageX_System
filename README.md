# 🏢 ManageX System

**ManageX** – Nesne Tabanlı Programlama dersi kapsamında geliştirilen bir **Şirket Arayüzü ve Yönetim Sistemi** projesidir.

PHP, MySQL, HTML, CSS ve JavaScript kullanılarak geliştirilmiştir.  
Amaç; **rol bazlı yetkilendirme**, **veri kalıcılığı**, **CRUD işlemleri** ve **gerçek hayata uygun iş akışları** ile kapsamlı bir yönetim paneli sunmaktır.

---

## 🚀 Özellikler

### 👥 Kullanıcı Sistemi (Register & Login)
- Yeni hesap oluşturma (Kayıt Ol)
- Mevcut hesap ile giriş yapma
- Şifreli giriş sistemi
- **Rol bazlı yönlendirme**
  - Yönetici
  - Çalışan
  - Misafir

---

### 🧾 Çalışan Yönetimi (CRUD)
- Yönetici ve Admin yetkileri
- Çalışan ekleme, silme, güncelleme
- Çalışan detay görüntüleme
- Çalışan bilgileri:
  - Ad – Soyad
  - Departman
  - Pozisyon
  - Telefon
  - E-posta
  - Fotoğraf (upload)

---

### 📅 Randevu Sistemi (Çakışma Kontrollü)
- Çalışan ve misafir kullanıcılar randevu talep edebilir
- **Randevu çakışma kontrolü**
  - Aynı zaman aralığında ikinci randevu alınamaz
  - Randevular gerçek hayata uygun şekilde bloklanır
- Kullanıcı randevu durumu:
  - Bekliyor
  - Onaylandı
  - Reddedildi
- Yönetici randevu onay / red işlemleri

---

### 🗂️ Görev Yönetimi Sistemi
- Yönetici tarafından görev oluşturma
- Görev özellikleri:
  - Başlık
  - Açıklama
  - Öncelik (Düşük / Orta / Yüksek)
  - Son tarih
  - Atanan kullanıcı
- Görev durumu:
  - Bekliyor
  - Yapıldı
- Filtreleme:
  - Duruma göre filtreleme
  - Arama (başlık & açıklama)
- Görev düzenleme ve silme
- Rol bazlı yetkilendirme
  - Misafir görev ekleyemez
  - Sadece yetkili roller işlem yapabilir

---

### 📍 Şirket Konumu & Harita Entegrasyonu
- Google Maps entegrasyonu
- Şirket konumu sabit olarak gösterilir.
- **Tüm kullanıcı rollerine açık**

---

### 📊 Dashboard (Kontrol Paneli)
- Rol bazlı içerik
- Yönetici & Admin için:
  - Toplam kullanıcı sayısı
  - Çalışan sayısı
  - Bekleyen randevular
  - Onaylanan randevular
- Net menü yapısı ve hızlı erişim

---

### 🎨 Arayüz & Kullanılabilirlik
- Özgün ve sade tasarım (hazır template kullanılmadı)
- Responsive yapı
- Butonlar ve menüler üzerinden tüm işlemler
- Değerlendirme sırasında:
  - Menü yerleşimi
  - Buton konumu
  - Buton metinleri
  - Tema renkleri
  **kolayca değiştirilebilir**

---

## 🧱 Klasör Yapısı

manageX_system/
│
├── classes/
│ ├── Database.php
│ ├── User.php
│ ├── Employee.php
│ └── Task.php
│
├── uploads/
│ └── employees/
│
├── appointment.php
├── appointments_admin.php
├── dashboard.php
├── employee.php
├── employee_view.php
├── index.php
├── login.php
├── logout.php
├── register.php
├── tasks.php
├── map.php
├── users_admin.php
└── managex.sql

---

## ⚙️ Kullanılan Teknolojiler

**PHP** --> Backend geliştirme, OOP yapı 
**MySQL** --> Veritabanı ve veri kalıcılığı 
**HTML / CSS / JS** --> Arayüz ve etkileşim 
**Google Maps** --> Harita ve yol tarifi 
**Git & GitHub** --> Versiyon kontrolü 

---

## 🧠 Rol Bazlı Yetkiler

- 👑 **Admin**
  - Kullanıcı yönetimi (CRUD)
  - Çalışan yönetimi
  - Randevu yönetimi
  - Görev yönetimi
  - İstatistikleri görür

- 🧍 **Çalışan**
  - Randevu talebi oluşturabilir
  - Görevleri görüntüler ve işaretleme yapabilir
  - Harita ve yol tarifi alabilir

- 🙋 **Misafir**
  - Randevu talebi oluşturabilir
  - Harita ve yol tarifi alabilir

---

## 🗄️ Veritabanı

- Veriler MySQL üzerinde tutulur
- Veritabanı adı: **managex**
- SQL yedeği: managex.sql

---

## 📌 Notlar

- Proje **eğitim amaçlıdır.**
- Şifreler hashlenmiştir.
- Kodlar içerisinde okuma kolaylığı sağlaması açısından **yorum satırları** eklenmiştir.

---

## 👨‍💻 Geliştirici

**Yağız Alp Sürmeneli**  
Trabzon Avrasya Üniversitesi  
Bilgisayar Programcılığı  

**© 2025 ManageX System**

