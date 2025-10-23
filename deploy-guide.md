# Lucky Draw Wheel App - Deployment Guide

## 🚀 XAMPP Setup (Local Development)

### 1. Install XAMPP

-   Download XAMPP from https://www.apachefriends.org/
-   Install and start Apache + MySQL services

### 2. Setup Project

```bash
# Copy project to XAMPP htdocs folder
C:\xampp\htdocs\luckydraw2025\
```

### 3. Database Setup

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create database: `vpbankgame_luckydraw`
3. Create user: `vpbankgame_luckydraw` with password `VpBank2025!@#`
4. Grant all privileges to user on database
5. Import file: `database.sql`
6. Or run: `install.php` to check setup

### 4. Test Application

-   Open: http://localhost/luckydraw2025/
-   Run: http://localhost/luckydraw2025/install.php (check setup)

---

## 🌐 DirectAdmin Deployment (Production)

### 1. Upload Files

Upload all files to your domain's public_html folder:

```
public_html/
├── index.php
├── process.php
├── config.php
├── .htaccess
├── api/
├── assets/
└── database.sql
```

### 2. Database Configuration

1. Login to DirectAdmin
2. Go to "MySQL Management"
3. Create database: `vpbankgame_luckydraw`
4. Create database user: `vpbankgame_luckydraw` with password `VpBank2025!@#`
5. Grant all privileges to user on database
6. Database configuration is already set in `config.php`:

```php
$db_config = [
    'host' => 'localhost',
    'dbname' => 'vpbankgame_luckydraw',
    'username' => 'vpbankgame_luckydraw',
    'password' => 'VpBank2025!@#',
    'charset' => 'utf8mb4'
];

$app_config = [
    'debug' => false, // Set to false for production
    // ... other settings
];
```

### 3. Import Database

1. Go to phpMyAdmin in DirectAdmin
2. Select your database
3. Import `database.sql` file

### 4. File Permissions

Set proper permissions:

```bash
chmod 644 *.php
chmod 644 .htaccess
chmod 755 assets/
chmod 644 assets/css/*
chmod 644 assets/js/*
chmod 644 assets/images/*
```

### 5. Test Production

-   Visit your domain: https://yourdomain.com/
-   Test all functionality
-   Check error logs if needed

---

## 🔧 Configuration Files

### config.php

Main configuration file for database and app settings.

### .htaccess

Apache configuration for:

-   Security headers
-   CORS for API
-   File compression
-   Cache control

### api/config.php

API-specific configuration that includes main config.

---

## 🛠️ Troubleshooting

### Common Issues:

1. **Database Connection Failed**

    - Check database credentials in `config.php`
    - Ensure database exists
    - Verify user permissions

2. **404 Errors**

    - Check `.htaccess` file exists
    - Verify mod_rewrite is enabled
    - Check file paths

3. **CORS Issues**

    - Check `.htaccess` CORS headers
    - Verify API endpoints

4. **Images Not Loading**
    - Check file permissions
    - Verify file paths in CSS
    - Check .htaccess for image blocking

### Debug Mode:

Set `debug => true` in `config.php` to see detailed errors.

---

## 📱 Mobile Testing

Test on different devices:

-   iPhone Safari
-   Android Chrome
-   Tablet browsers
-   Different screen sizes

---

## 🔒 Security Checklist

-   [ ] Set `debug => false` in production
-   [ ] Use strong database passwords
-   [ ] Enable HTTPS
-   [ ] Regular backups
-   [ ] Monitor error logs
-   [ ] Update dependencies

---

## 📊 Monitoring

### DirectAdmin Logs:

-   Error logs: `/logs/yourdomain.com.error.log`
-   Access logs: `/logs/yourdomain.com.access.log`

### Database Monitoring:

-   Check participant count
-   Monitor for duplicate entries
-   Regular backups

---

## 🚀 Performance Optimization

1. **Enable Gzip Compression** (in .htaccess)
2. **Set Cache Headers** (in .htaccess)
3. **Optimize Images** (compress PNG files)
4. **Minify CSS/JS** (for production)

---

## 📞 Support

If you encounter issues:

1. Check error logs
2. Verify configuration
3. Test on local XAMPP first
4. Check DirectAdmin documentation
