# Prompt tạo video AI — "Hành trình từ nông trại đến tách trà" (Calen)

Theo đúng 7 cảnh sếp yêu cầu. Dùng cho Google Veo, Kling, Runway Gen-3, Sora, Pika, Hailuo…
Phần lớn công cụ tạo clip 5–10 giây, nên quay **từng cảnh rồi ghép**. Prompt viết bằng tiếng Anh vì các tool chạy tốt hơn với tiếng Anh.

---
## ⭐ PROMPT CHUẨN — VIDEO 40 GIÂY (dán 1 lần)
Dùng cho tool nhận cả kịch bản có mốc thời gian (**Sora, Veo 3, Kling 2.x**). Dán nguyên khối dưới đây:

```
A 40-second cinematic documentary brand video for a Vietnamese herbal tea company. Style: warm natural morning light, earthy green and wood tones, shallow depth of field, soft film grain, realistic 4K, 16:9. One consistent warm, calm Vietnamese FEMALE voiceover narrates throughout; soft gentle instrumental background music, no lyrics, no other voices. Smooth slow camera moves, one clear subject per shot.

0:00–0:05 — Aerial drone slowly rising over a lush Vietnamese herbal farm at sunrise, golden light flooding rows of green plants, light mist, dew on leaves. Voiceover: "Mỗi ngày của Calen bắt đầu từ nông trại, khi mặt trời vừa lên."
0:05–0:10 — Close-up of a farmer's hands gently picking fresh green herbs into a bamboo basket, soft morning light. Voiceover: "Thảo mộc được chọn hái bằng tay, đúng độ, đúng mùa."
0:10–0:15 — Baskets of fresh herbs carried into a clean bright herbal workshop, daylight through windows. Voiceover: "Ngay trong ngày, nguyên liệu tươi về đến xưởng."
0:15–0:20 — Fresh green herbs washed in clear running water in a stainless steel basin, droplets in slow motion. Voiceover: "Rửa sạch kỹ càng từng mẻ."
0:20–0:25 — Herbs spread on drying trays in a low-temperature dryer, gentle warm air, macro detail. Voiceover: "Sấy ở nhiệt độ thấp, giữ trọn màu và hương tự nhiên."
0:25–0:30 — Dried herbs ground into small pieces and blended by recipe, warm workshop light, close-up. Voiceover: "Nghiền mịn và phối trộn theo công thức riêng của Calen."
0:30–0:35 — A machine filling small tea filter bags, bags packed into kraft boxes, sealed and stacked for shipping. Voiceover: "Đóng thành túi lọc, vào hộp, sẵn sàng đến tay bạn."
0:35–0:40 — Hero product shot: a red herbal tea box beside a freshly brewed cup of tea on a wooden table, steam rising, warm cozy light. Voiceover: "Calen Nature's Farm — trà thảo mộc sạch từ nông trại. Nâng niu vẻ đẹp Á Đông."

Negative prompt: text, letters, words, captions, logo, watermark, cartoon, distorted hands, extra fingers, deformed faces, morphing, flickering, blurry, oversaturated.
```

> **Cảnh cuối (0:35–0:40):** AI vẽ hộp sẽ chỉ na ná. Để đúng thương hiệu, sau khi dựng hãy **thay/đè bằng hộp Calen thật** (ảnh UG-UXO bạn gửi) — hoặc tạo riêng cảnh cuối bằng **image-to-video** từ ảnh hộp thật rồi ghép vào.
> **Nếu tool chỉ tạo được 5–10 giây/clip** (Runway, Pika): dùng 8 prompt tách rời ở mục bên dưới, mỗi clip 5 giây, rồi ghép thành 40 giây.

---
## Thiết lập chung (dán kèm mỗi cảnh)
- **Style:** cinematic documentary, warm natural morning light, shallow depth of field, soft film grain, earthy green and wood tones, realistic, 4K.
- **Aspect ratio:** 16:9 cho web (làm thêm bản 9:16 cho Reels/TikTok).
- **Negative prompt (loại bỏ):** `text, letters, words, captions, logo, watermark, cartoon, distorted hands, extra fingers, oversaturated`
- **Lưu ý:** không để chữ quảng cáo công dụng xuất hiện trong khung hình.

## 8 cảnh — MỖI CẢNH ĐỀU CÓ LỒNG TIẾNG (~6–8 giây)

Mỗi cảnh gồm 2 phần: **hình** (dòng tiếng Anh) và **lồng tiếng** (dòng `Audio:`).
- Với tool hỗ trợ âm thanh (**Google Veo 3, Kling 2.x có audio**): dán cả 2 dòng, tool tự đọc giọng theo cảnh.
- Với tool chỉ tạo hình (Runway, Pika, Sora bản không audio): tạo hình trước, rồi **thu/ghép giọng đúng câu đó** ở bước dựng.
- Giọng xuyên suốt: **một người, nữ (hoặc nam) miền Bắc, ấm, chậm, tin cậy** — giữ cùng một giọng cho cả 8 cảnh.

**Cảnh 1 — Nông trại đón nắng sớm**
`Aerial drone shot slowly rising over a lush Vietnamese herbal farm at sunrise, golden morning sunlight flooding rows of green medicinal plants, light mist, dew on leaves, peaceful, cinematic, 4K.`
`Audio: warm calm Vietnamese female voiceover says: "Mỗi ngày của Calen bắt đầu từ nông trại, khi mặt trời vừa lên." Soft gentle background music, no other voices.`

**Cảnh 2 — Thu hái dược liệu**
`Close-up of a farmer's hands gently picking fresh green herbal leaves and flowers into a bamboo basket, soft morning light, shallow depth of field, authentic rural Vietnam, cinematic documentary.`
`Audio: same warm Vietnamese female voiceover says: "Thảo mộc được chọn hái bằng tay, đúng độ, đúng mùa." Soft background music.`

**Cảnh 3 — Về xưởng trong ngày**
`Baskets of fresh herbs being carried into a clean herbal processing workshop, daylight through large windows, workers in aprons, calm and organized, cinematic.`
`Audio: same warm Vietnamese female voiceover says: "Ngay trong ngày, nguyên liệu tươi về đến xưởng." Soft background music.`

**Cảnh 4 — Rửa sạch**
`Fresh green herbs being washed in clear running water in a stainless steel basin, water droplets splashing in slow motion, clean and hygienic, close-up, cinematic.`
`Audio: same warm Vietnamese female voiceover says: "Rửa sạch kỹ càng từng mẻ." Gentle water sounds, soft music.`

**Cảnh 5 — Sấy khô**
`Herbs spread evenly on drying trays inside a low-temperature dryer, gentle warm air, herbs slowly drying and changing to deep green-brown, soft light, macro detail, cinematic.`
`Audio: same warm Vietnamese female voiceover says: "Sấy ở nhiệt độ thấp, giữ trọn màu và hương tự nhiên." Soft background music.`

**Cảnh 6 — Nghiền & phối trộn**
`Dried herbs being ground into small pieces and measured on a scale, hands blending different dried herbs together by recipe, warm workshop light, close-up, cinematic.`
`Audio: same warm Vietnamese female voiceover says: "Nghiền mịn và phối trộn theo công thức riêng của Calen." Soft background music.`

**Cảnh 7 — Đóng túi lọc & gửi đi**
`A machine filling small tea filter bags with ground herbs, bags packed into kraft paper boxes, boxes sealed and stacked ready for shipping, clean modern workshop, satisfying process, cinematic.`
`Audio: same warm Vietnamese female voiceover says: "Đóng thành túi lọc, vào hộp, sẵn sàng đến tay bạn." Soft background music.`

**Cảnh 8 (BẮT BUỘC) — Hộp trà Calen ở đoạn cuối**
> Cảnh này phải có **hộp trà Calen thật**. AI không vẽ lại đúng nhãn/chữ trên hộp được, nên làm 1 trong 2 cách:
> - **Cách tốt nhất (khuyên dùng):** chụp/quay **hộp Calen thật** (như ảnh UG-UXO bạn gửi), rồi dùng tính năng **image-to-video** của Kling / Runway / Veo để cho hộp xoay nhẹ, khói trà bay. Giữ đúng thương hiệu 100%.
> - **Nếu tạo hoàn toàn bằng AI (hộp sẽ chỉ na ná, không chính xác):**
> `A red herbal tea box standing on a wooden table next to a freshly brewed cup of tea, steam rising, herbs beside it, warm cozy morning light, product hero shot, cinematic, shallow depth of field.`
> Sau đó **dán logo + tên Calen thật đè lên** ở bước dựng để đúng thương hiệu.
>
> Lồng tiếng cảnh cuối: `Audio: same warm Vietnamese female voiceover says: "Calen Nature's Farm — trà thảo mộc sạch từ nông trại. Nâng niu vẻ đẹp Á Đông." Soft music swells gently.`

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
