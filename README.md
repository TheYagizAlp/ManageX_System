# 🏢 ManageX System

**ManageX** – Nesne Tabanlı Programlama dersi kapsamında geliştirilen bir **Şirket Arayüzü ve Yönetim Sistemi** projesidir.

PHP, MySQL, HTML, CSS ve JavaScript kullanılarak geliştirilmiştir.  
Amaç; **rol bazlı yetkilendirme**, **veri kalıcılığı**, **CRUD işlemleri** ve **gerçek hayata uygun iş akışları** ile kapsamlı bir yönetim paneli sunmaktır.

---

## 🧱 Klasör Yapısı

manageX_system/
│
├── classes/
│   ├── Database.php
│   ├── User.php
│   ├── Employee.php
│   └── Task.php
│
├── uploads/
│   └── employees/
│       └── (çalışan fotoğrafları)
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
│
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

