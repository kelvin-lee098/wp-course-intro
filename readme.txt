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
* `[wci_course_carousel limit="12" per_view="4" interval="5000" arrows="yes" orderby="rand"]` — carousel chạy khóa học.
  * `per_view` — số khóa hiển thị cùng lúc trên màn hình lớn (1-6); tự giảm còn 3 / 2 / 1 ở màn nhỏ hơn.
  * `interval` — mili-giây giữa mỗi lần tự trượt (tối thiểu 1500; đặt `0` để tắt tự chạy).
  * `arrows` — `yes`/`no` hiển thị nút mũi tên.

=== Đổi số khóa hiển thị cho carousel "Các khóa học khác" (trang chi tiết) ===

Thêm vào `functions.php` của theme:

`add_filter( 'wci_carousel_defaults', function ( $d ) {
	$d['per_view'] = 3;   // số khóa / màn hình
	$d['interval'] = 4000; // ms giữa mỗi lần trượt (0 = tắt tự chạy)
	return $d;
} );`

=== Tuỳ biến giao diện (theme override) ===

Sao chép các file sau vào thư mục theme để tự chỉnh:

* `templates/single-course.php`  → `your-theme/single-wci_course.php`
* `templates/archive-course.php` → `your-theme/archive-wci_course.php`
* `templates/parts/*.php`        → `your-theme/wp-course-intro/*.php`

== Changelog ==

= 1.0.4 =
* Sửa lỗi CSS/JS của [wci_courses]/[wci_course_carousel] không nạp khi shortcode nằm ở trang thường (không phải trang chi tiết khóa học) — trước đó plugin gọi wp_enqueue_style() ngay trong lúc render shortcode, tức sau khi <head> đã in xong nên style bị rớt. Giờ style được nhận diện sớm (has_shortcode) để in đúng trong <head>, và có lưới an toàn in bù ở footer cho các trường hợp page builder/widget không quét được.

= 1.0.3 =
* Carousel chuyển sang kiểu "phân trang": hiển thị N khóa/màn hình, tự trượt đúng 1 trang sau mỗi khoảng thời gian, có chấm phân trang. Thêm tham số per_view / interval / arrows và filter wci_carousel_defaults.

= 1.0.2 =
* Sửa lỗi carousel "Các khóa học khác" tràn ra ngoài container làm lệch trang (thêm min-width:0 / overflow containment cho các wrapper).

= 1.0.1 =
* Bootstrap chống lỗi: thiếu file hoặc PHP quá cũ chỉ hiện thông báo trong admin, không sập site. Dời khởi tạo sang plugins_loaded.

= 1.0.0 =
* Phiên bản đầu tiên.
