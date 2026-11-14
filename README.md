# Calen Nature's Farm — website trà thảo mộc

Mã nguồn của trang bán hàng calennaturesfarm.com (WordPress + WooCommerce + theme Kadence, chạy trên Hostinger).

## Có gì trong repo

| Thư mục | Nội dung |
|---|---|
| `calen-setup/` | Plugin WordPress chính: cài đặt cửa hàng (VND, COD, miễn phí giao hàng mọi đơn, menu, màu, font), thanh thông báo, nút Zalo/Gọi, trang sản phẩm |
| `calen-tests/` | Kiểm tra tự động: bậc giá, số điện thoại, mô tả SEO, thành phần (%) — chạy `for t in calen-tests/*.php; do php -d zend.assertions=1 -d assert.exception=1 "$t"; done` |
| `calen-design/` | Bản thiết kế trang chủ và trang sản phẩm (Claude Design) |
| `assets/` | Ảnh minh hoạ do AI tạo, cần thay bằng ảnh thật |
| `tools/preview_proxy.py` | Xem trước trang trên máy khi chưa có tên miền |

Nội dung trang (trang chủ, sản phẩm, đơn hàng) nằm trong cơ sở dữ liệu WordPress, không có trong repo này.

## Giá bán

Giá web từ 199.000₫ đến 210.000₫, chia theo mức giá hiện tại của từng loại trên Shopee (`CALEN_PRICE_TIERS` trong `calen-setup/calen-pricing.php`, áp một lần):

| Giá trên Shopee | Giá trên web |
|---|---|
| Dưới 130.000₫ | 199.000₫ |
| 130.000₫ – 169.999₫ | 205.000₫ |
| Từ 170.000₫ | 210.000₫ |

Sau đó đổi giá từng sản phẩm trong WordPress Admin như bình thường; plugin không ghi đè.

## Đưa plugin lên web

Nén thư mục `calen-setup/` thành `calen-setup.zip`, rồi vào WordPress Admin → Plugins → Add New → Upload Plugin.

## Xem trước khi chưa có tên miền

```bash
python3 -I tools/preview_proxy.py 8790
```

Sau đó mở http://localhost:8790.
