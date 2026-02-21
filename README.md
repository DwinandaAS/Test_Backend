# Test_Backend
Author
Nama: Dwinanda Alfauzan Suhando
Tanggal: 21-02-2026
BANDUNG

# ⚠️ POSTGRESQL SETUP REQUIRED

Untuk menggunakan PostgreSQL sesuai keinginan Anda, perlu 1 langkah tambahan:

## 🔧 Langkah-Langkah:

### 1. Find PHP Configuration
```bash
# Cari lokasi php.ini
php --ini
```

### 2. Enable PostgreSQL Driver
Di file `php.ini`, uncomment atau tambahkan:
```ini
extension=pdo_pgsql
```

### 3. Restart PHP Server
```bash
# Stop server yang berjalan (Ctrl+C)
# Kemudian jalankan ulang:
php -S localhost:8000
```

### 4. Setup Database
```bash
php setup_postgresql.php
```

---

## ✅ Jika PostgreSQL Extension Sudah Enabled:

```bash
# Setup database
php setup_postgresql.php

# Start server
php -S localhost:8000

# Test API
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","password":"testpass123"}'
```

---

## 📋 Checklist PostgreSQL Setup:

- [ ] PostgreSQL Server running on localhost:5432
- [ ] Database `db_test` sudah created
- [ ] `pdo_pgsql` extension enabled di php.ini
- [ ] Run `php setup_postgresql.php`
- [ ] API Server berjalan di localhost:8000
- [ ] Login endpoint berhasil return JWT token

---

## 🆘 Alternative (Jika PostgreSQL Extension tidak bisa diinstall):

Gunakan MySQL/MariaDB atau SQLite sebagai temporary workaround:

```bash
# Untuk SQLite:
php setup_sqlite.php
php -S localhost:8000

# Untuk MySQL: 
# Update database.php ke driver pdo dengan MySQL DSN
```
Database Create Tabel Users

-- Create users table jika belum ada
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

Swagger Documentation API 

http://localhost:8000/swagger/index.html
