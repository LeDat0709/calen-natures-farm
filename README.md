# Calen Nature's Farm — website trà thảo mộc

Mã nguồn của trang bán hàng calennaturesfarm.com (WordPress + WooCommerce + theme Kadence, chạy trên Hostinger).

## Có gì trong repo

| Thư mục | Nội dung |
|---|---|
| `calen-setup/` | Plugin WordPress chính: cài đặt cửa hàng (VND, COD, phí ship 30.000₫, menu, màu, font), thanh thông báo, nút Zalo/Gọi, giá combo, trang sản phẩm |
| `calen-setup/calen-tiers.php` | Mức giảm combo: mua 3 gói giảm 7%, mua 6 gói giảm 12% |
| `calen-tests/` | Kiểm tra cách tính giá combo |
| `calen-design/` | Bản thiết kế trang chủ và trang sản phẩm (Claude Design) |
| `assets/` | Ảnh minh hoạ do AI tạo, cần thay bằng ảnh thật |
| `tools/preview_proxy.py` | Xem trước trang trên máy khi chưa có tên miền |

Nội dung trang (trang chủ, sản phẩm, đơn hàng) nằm trong cơ sở dữ liệu WordPress, không có trong repo này.

## Đổi mức giảm combo

Sửa `CALEN_TIERS` trong `calen-setup/calen-tiers.php`, rồi chạy kiểm tra:

```bash
php -d zend.assertions=1 -d assert.exception=1 calen-tests/tier-check.php
```

## Đưa plugin lên web

Nén thư mục `calen-setup/` thành `calen-setup.zip`, rồi vào WordPress Admin → Plugins → Add New → Upload Plugin.

## Xem trước khi chưa có tên miền

```bash
python3 -I tools/preview_proxy.py 8790
```

Sau đó mở http://localhost:8790.
