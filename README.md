# 🏢 ManageX System

**ManageX** – Nesne Tabanlı Programlama dersi kapsamında geliştirilen bir **şirket yönetim sistemi** projesidir.  
PHP, MySQL, HTML, CSS ve JavaScript kullanılarak geliştirilmiştir.  
Amaç, kullanıcı rolleri ve CRUD temelli işlemlerle gerçek bir yönetim paneli deneyimi oluşturmaktır.

---

## 🚀 Özellikler

- 👥 **Kullanıcı Sistemi (Register & Login)**
  - Kullanıcı, Yönetici ve Admin rolleri desteklenir.
  - Rol bazlı yönlendirme yapılır.
- 🧾 **Çalışan Yönetimi (CRUD)**
  - Yönetici, çalışan ekleme/silme/güncelleme işlemleri yapabilir.
  - Çalışan görselleri ve departman bilgileri tutulur.
- 📅 **Randevu Sistemi**
  - Kullanıcılar randevu oluşturabilir.
  - Doluluk kontrolü sağlanır.
- 📍 **Harita Paneli**
  - Şirket konumu (Avrasya Üniversitesi) Google Maps üzerinde sabitlenmiştir.
- 🖥️ **Dashboard**
  - Yönetici ve admin için istatistiksel özet paneli.
- 🎨 **Modern & Konforlu Tasarım**
  - Renk paleti: Yeşil & turkuaz uyumlu.
  - Tam responsive yapı.

---

## 🧱 Klasör Yapısı

- **manageX_system**
  - **classes/**
    - `Database.php`  
    - `User.php`  
    - `Employee.php`
  - **uploads/**
    - `employees/` → Çalışan görselleri burada tutulur.
  - **Ana dosyalar**
    - `index.php` → Giriş ekranı  
    - `login.php` → Giriş işlemleri  
    - `register.php` → Kayıt olma sayfası  
    - `employee.php` → Çalışan yönetimi  
    - `employee_view.php` → Çalışan detay ekranı  
    - `appointment.php` → Kullanıcı randevu sistemi  
    - `appointments_admin.php` → Yönetici randevu yönetimi  
    - `users_admin.php` → Admin kullanıcı yönetimi  
    - `dashboard.php` → Özet panel ekranı 

---

## ⚙️ Kullanılan Teknolojiler

| Teknoloji | Açıklama |
|------------|-----------|
| **PHP** | Backend geliştirme ve OOP yapısı |
| **MySQL** | Veritabanı yönetimi |
| **HTML/CSS/JS** | Arayüz ve etkileşimli tasarım |
| **Google Maps API** | Şirket konumu gösterimi |
| **Git/GitHub** | Versiyon kontrol sistemi |

---

## 🧠 Rollere Göre Yetkiler

| Rol --> Yetkiler |
-----------------------
| 👑 **Admin** --> Tüm kullanıcıları yönetir (CRUD), sistem genelini görebilir. |
| 🧍 **Manager (Yönetici)** --> Çalışan yönetimi yapabilir, harita paneline erişir. |
| 🙋 **User (Kullanıcı)** --> Randevu oluşturabilir, doluluk durumunu görebilir. |

---

## 🧾 Not

> Bu proje eğitim amacıyla geliştirilmiştir.  
> Veriler `managex` isimli MySQL veritabanında tutulmaktadır.

---

**© 2025 ManageX System — Developed by Yağız Alp**
