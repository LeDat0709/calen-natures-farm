# Prompt tạo video AI — "Hành trình từ nông trại đến tách trà" (Calen)

Theo đúng 7 cảnh sếp yêu cầu. Dùng cho Google Veo, Kling, Runway Gen-3, Sora, Pika, Hailuo…
Phần lớn công cụ tạo clip 5–10 giây, nên quay **từng cảnh rồi ghép**. Prompt viết bằng tiếng Anh vì các tool chạy tốt hơn với tiếng Anh.

## Thiết lập chung (dán kèm mỗi cảnh)
- **Style:** cinematic documentary, warm natural morning light, shallow depth of field, soft film grain, earthy green and wood tones, realistic, 4K.
- **Aspect ratio:** 16:9 cho web (làm thêm bản 9:16 cho Reels/TikTok).
- **Negative prompt (loại bỏ):** `text, letters, words, captions, logo, watermark, cartoon, distorted hands, extra fingers, oversaturated`
- **Lưu ý:** không để chữ quảng cáo công dụng xuất hiện trong khung hình.

## 7 cảnh (mỗi cảnh ~6–8 giây)

**Cảnh 1 — Nông trại đón nắng sớm**
`Aerial drone shot slowly rising over a lush Vietnamese herbal farm at sunrise, golden morning sunlight flooding rows of green medicinal plants, light mist, dew on leaves, peaceful, cinematic, 4K.`

**Cảnh 2 — Thu hái dược liệu**
`Close-up of a farmer's hands gently picking fresh green herbal leaves and flowers into a bamboo basket, soft morning light, shallow depth of field, authentic rural Vietnam, cinematic documentary.`

**Cảnh 3 — Về xưởng trong ngày**
`Baskets of fresh herbs being carried into a clean herbal processing workshop, daylight through large windows, workers in aprons, calm and organized, cinematic.`

**Cảnh 4 — Rửa sạch**
`Fresh green herbs being washed in clear running water in a stainless steel basin, water droplets splashing in slow motion, clean and hygienic, close-up, cinematic.`

**Cảnh 5 — Sấy khô**
`Herbs spread evenly on drying trays inside a low-temperature dryer, gentle warm air, herbs slowly drying and changing to deep green-brown, soft light, macro detail, cinematic.`

**Cảnh 6 — Nghiền & phối trộn**
`Dried herbs being ground into small pieces and measured on a scale, hands blending different dried herbs together by recipe, warm workshop light, close-up, cinematic.`

**Cảnh 7 — Đóng túi lọc & gửi đi**
`A machine filling small tea filter bags with ground herbs, bags packed into kraft paper boxes, boxes sealed and stacked ready for shipping, clean modern workshop, satisfying process, cinematic.`

**Cảnh 8 (BẮT BUỘC) — Hộp trà Calen ở đoạn cuối**
> Cảnh này phải có **hộp trà Calen thật**. AI không vẽ lại đúng nhãn/chữ trên hộp được, nên làm 1 trong 2 cách:
> - **Cách tốt nhất (khuyên dùng):** chụp/quay **hộp Calen thật** (như ảnh UG-UXO bạn gửi), rồi dùng tính năng **image-to-video** của Kling / Runway / Veo để cho hộp xoay nhẹ, khói trà bay. Giữ đúng thương hiệu 100%.
> - **Nếu tạo hoàn toàn bằng AI (hộp sẽ chỉ na ná, không chính xác):**
> `A red herbal tea box standing on a wooden table next to a freshly brewed cup of tea, steam rising, herbs beside it, warm cozy morning light, product hero shot, cinematic, shallow depth of field.`
> Sau đó **dán logo + tên Calen thật đè lên** ở bước dựng để đúng thương hiệu.

## Lời bình (lồng tiếng) — đọc theo từng cảnh
Giọng ấm, chậm, tin cậy (nam hoặc nữ miền Bắc). Tổng ~45–55 giây. **Không nói công dụng chữa bệnh**, chỉ nói về quy trình, sự tử tế, chất lượng.

| Cảnh | Lời đọc |
|---|---|
| 1 | "Mỗi ngày của Calen bắt đầu từ nông trại, khi mặt trời vừa lên." |
| 2 | "Thảo mộc được chọn hái bằng tay, đúng độ, đúng mùa." |
| 3 | "Ngay trong ngày, nguyên liệu tươi về đến xưởng." |
| 4 | "Rửa sạch kỹ càng từng mẻ." |
| 5 | "Sấy ở nhiệt độ thấp, giữ trọn màu và hương tự nhiên." |
| 6 | "Nghiền mịn và phối trộn theo công thức riêng của Calen." |
| 7 | "Đóng thành túi lọc, vào hộp, sẵn sàng đến tay bạn." |
| 8 | "Calen Nature's Farm — trà thảo mộc sạch từ nông trại. Nâng niu vẻ đẹp Á Đông." |

**Cách lồng tiếng để tránh giọng robot:** đọc thật và thu âm, hoặc dùng giọng AI tự nhiên (ElevenLabs, hoặc giọng Vbee/FPT.AI tiếng Việt). Đừng để công cụ tạo video tự sinh giọng — thường bị méo, sai dấu.

## Tránh lỗi thường gặp khi tạo video AI
1. **Không nhờ AI vẽ chữ trong khung hình** — AI tạo chữ luôn méo, sai chính tả. Mọi chữ (tên trà, logo, "Freeship"…) **thêm ở bước dựng** (CapCut, Premiere, hoặc gắn ảnh thật).
2. **Hạn chế cảnh cận bàn tay/nhiều ngón tay** — AI hay thừa/thiếu ngón. Nếu cần tay (hái, rửa), quay **góc rộng hơn** hoặc chọn bản không lỗi.
3. **Mỗi cảnh một chuyển động chậm, một chủ thể rõ** — pan hoặc push-in nhẹ. Tránh nhiều vật chuyển động cùng lúc → dễ bị nhoè, biến dạng.
4. **Luôn dùng negative prompt** ở mỗi cảnh: `text, letters, words, logo, watermark, distorted hands, extra fingers, deformed, morphing, flickering, blurry, oversaturated`.
5. **Tạo mỗi cảnh 2–3 lần, chọn bản sạch nhất** — AI video còn ngẫu nhiên, lần đầu hiếm khi hoàn hảo.
6. **Giữ tông màu nhất quán** — dán cùng câu style (warm morning light, earthy green & wood tones) vào mọi cảnh để 8 clip trông cùng một phim.
7. **Hộp Calen: dùng ảnh thật** (image-to-video), đừng để AI tự dựng nhãn.

## Sau khi có clip
- Ghép 8 cảnh theo đúng thứ tự, mỗi cảnh 5–7 giây (tổng ~45–55 giây).
- Lồng tiếng theo bảng trên + nhạc nền nhẹ không lời (để nhạc nhỏ hơn giọng đọc).
- Gắn tên/logo Calen và hộp trà thật ở cảnh cuối.
- Gửi mình file hoặc link YouTube/Facebook, mình gắn vào mục "Hành trình từ nông trại đến tách trà" trên trang chủ, thay dòng "video đang cập nhật".
