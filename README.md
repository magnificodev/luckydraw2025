# Lucky Draw Wheel App - VPBank Solution Day

Ứng dụng vòng quay may mắn cho sự kiện VPBank Solution Day với 3 màn hình tương tác.

## Tính năng

-   **Màn hình 1**: Nhập số điện thoại với validation format Việt Nam
-   **Màn hình 2**: Vòng quay may mắn với 8 phần quà có tỷ lệ trúng bằng nhau
-   **Màn hình 3**: Hiển thị phần quà đã trúng
-   **Logic đặc biệt**: Nếu số điện thoại đã quay rồi, bỏ qua màn 2 và hiển thị trực tiếp phần quà cũ

## Công nghệ sử dụng

-   **Frontend**: PHP 7.4+ với HTML5, CSS3, JavaScript
-   **Backend**: PHP 7.4+ với MySQL
-   **Database**: MySQL 5.7+
-   **Assets**: PNG images cho backgrounds, buttons, wheel, gifts

## Cài đặt

### 🚀 XAMPP (Local Development)

1. **Cài đặt XAMPP**

    - Download từ https://www.apachefriends.org/
    - Start Apache + MySQL services

2. **Setup Project**

    - Copy project vào `C:\xampp\htdocs\luckydraw2025\`
    - Mở http://localhost/luckydraw2025/

3. **Database Setup**
    - Mở phpMyAdmin: http://localhost/phpmyadmin
    - Tạo database: `vpbankgame_luckydraw`
    - Tạo user: `vpbankgame_luckydraw` với password `VpBank2025!@#`
    - Grant all privileges cho user trên database
    - Import file `database.sql`
    - Hoặc chạy `install.php` để check setup

### 🌐 DirectAdmin (Production)

1. **Upload Files**

    - Upload tất cả files vào `public_html/`
    - Cấu hình database trong DirectAdmin

2. **Database Configuration**

    - Tạo database: `vpbankgame_luckydraw` trong DirectAdmin
    - Tạo user: `vpbankgame_luckydraw` với password `VpBank2025!@#`
    - Grant all privileges cho user trên database
    - Import `database.sql` (config đã sẵn sàng)

3. **File Permissions**
    - Set quyền phù hợp cho files
    - Enable mod_rewrite

Xem chi tiết trong file `deploy-guide.md`

### 4. Assets

Đảm bảo các file assets đã có trong thư mục `assets/images/`:

-   `background-1.png`, `background-2.png`, `background-3.png`
-   `start-button.png`, `spin-button.png`
-   `wheel.png`, `wheel-pointer.png`
-   `gifts/` folder với 8 file ảnh quà tặng

## Cấu trúc Project

```
luckydraw2025/
├── assets/
│   └── images/
│       ├── background-1.png
│       ├── background-2.png
│       ├── background-3.png
│       ├── start-button.png
│       ├── spin-button.png
│       ├── wheel.png
│       ├── wheel-pointer.png
│       └── gifts/
│           ├── binh-thuy-tinh.png
│           ├── bit-mat-ngu.png
│           ├── moc-khoa.png
│           ├── mu-bao-hiem.png
│           ├── o-gap.png
│           ├── tag-hanh-ly.png
│           ├── tai-nghe.png
│           └── tui-tote.png
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
│       ├── background-1.png
│       ├── background-2.png
│       ├── background-3.png
│       ├── start-button.png
│       ├── spin-button.png
│       ├── wheel.png
│       ├── wheel-pointer.png
│       └── gifts/
│           ├── binh-thuy-tinh.png
│           ├── bit-mat-ngu.png
│           ├── moc-khoa.png
│           ├── mu-bao-hiem.png
│           ├── o-gap.png
│           ├── tag-hanh-ly.png
│           ├── tai-nghe.png
│           └── tui-tote.png
├── api/
│   ├── config.php
│   ├── check-phone.php
│   └── spin.php
├── database.sql
├── index.php
├── process.php
└── README.md
```

## PHP Flow

### index.php

-   **Entry point**: Hiển thị 3 màn hình dựa trên URL parameter `?screen=1|2|3`
-   **Session management**: Lưu trữ phone number và prize trong PHP session
-   **Error display**: Hiển thị lỗi từ session

### process.php

-   **Form handler**: Xử lý form submission từ màn hình 1 và 2
-   **Phone validation**: Validate format số điện thoại Việt Nam
-   **Database operations**: Check existing phone, insert new records
-   **Redirect logic**: Chuyển hướng đến màn hình phù hợp

### Flow hoạt động:

1. **Màn 1**: User nhập SĐT → Submit form → `process.php` check database
2. **Màn 2**: User bấm "Quay" → Submit form → `process.php` random prize → Lưu DB
3. **Màn 3**: Hiển thị prize từ session

## Validation Rules

-   **Số điện thoại**: 10-11 số, bắt đầu bằng 0
-   **Format**: Chỉ chứa số, không có ký tự đặc biệt
-   **Uniqueness**: Mỗi số điện thoại chỉ được quay 1 lần

## Animation

-   **Wheel Spin**: 4 giây animation với easing function
-   **Multiple Rotations**: 5 vòng quay + góc đích + random offset
-   **Smooth Transition**: CSS transitions cho tất cả interactions

## Responsive Design

-   **Mobile-first**: Tối ưu cho màn hình di động
-   **Breakpoints**: 480px, 360px
-   **Touch-friendly**: Buttons và inputs dễ sử dụng trên mobile

## Browser Support

-   Chrome 60+
-   Firefox 55+
-   Safari 12+
-   Edge 79+

## Troubleshooting

### Lỗi kết nối database

-   Kiểm tra thông tin database trong `api/config.php`
-   Đảm bảo MySQL service đang chạy
-   Kiểm tra quyền truy cập database

### Lỗi CORS

-   Đảm bảo web server hỗ trợ CORS headers
-   Kiểm tra file `api/config.php` có đầy đủ headers

### Assets không hiển thị

-   Kiểm tra đường dẫn file assets
-   Đảm bảo quyền đọc file
-   Kiểm tra tên file có đúng case-sensitive

## License

Dự án này được tạo cho VPBank Solution Day 2025.
