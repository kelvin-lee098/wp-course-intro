=== WP Course Intro ===
Contributors: yourname
Tags: course, khoa hoc, education, custom post type
Requires at least: 5.6
Tested up to: 6.6
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Quản lý và giới thiệu khóa học với trang chi tiết đầy đủ và carousel "khóa học khác".

== Description ==

Plugin tạo một loại nội dung "Khóa học" (Custom Post Type) với đầy đủ thông tin:

* Ảnh đại diện (Featured image)
* Tiêu đề
* Mô tả ngắn
* Nội dung khóa học (trình soạn thảo)
* Yêu cầu đầu vào
* Hình thức học
* Cam kết đầu ra
* Giảng viên (ảnh + tên, thêm được nhiều người)
* Trợ giảng (ảnh + tên, thêm được nhiều người)
* Link đăng ký

Trang chi tiết tự động hiển thị toàn bộ thông tin theo bố cục 2 cột (nội dung + thẻ thông tin nhanh có nút đăng ký), và cuối trang là carousel chạy các khóa học khác để tham khảo.

== Cách dùng ==

1. Kích hoạt plugin.
2. Vào menu **Khóa học → Thêm khóa học mới**, điền thông tin, đặt Ảnh đại diện, bấm Đăng.
3. Xem khóa học ở đường dẫn `/khoa-hoc/ten-khoa-hoc/`.
4. Danh sách tất cả khóa học ở `/khoa-hoc/`.

=== Shortcode ===

* `[wci_courses limit="12" columns="3" orderby="date" order="DESC" exclude=""]` — lưới khóa học.
* `[wci_course_carousel limit="12" orderby="rand"]` — carousel chạy khóa học.

=== Tuỳ biến giao diện (theme override) ===

Sao chép các file sau vào thư mục theme để tự chỉnh:

* `templates/single-course.php`  → `your-theme/single-wci_course.php`
* `templates/archive-course.php` → `your-theme/archive-wci_course.php`
* `templates/parts/*.php`        → `your-theme/wp-course-intro/*.php`

== Changelog ==

= 1.0.0 =
* Phiên bản đầu tiên.
