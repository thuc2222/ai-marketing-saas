import os
import re
import json

# ================= CẤU HÌNH =================
# Các thư mục cần quét (tránh quét vendor/node_modules cho nhẹ)
SCAN_DIRS = ['app', 'resources', 'routes', 'config']
# File đích để lưu kết quả
OUTPUT_FILE = 'lang/vi.json'
# Các đuôi file cần đọc
EXTENSIONS = ('.php', '.blade.php')

# ================= REGEX (Biểu thức chính quy) =================
# Tìm các mẫu: __('text'), trans('text'), @lang('text')
# Giải thích:
# (?:__|trans|@lang) -> Tìm các hàm __ hoặc trans hoặc @lang
# \s*\(\s* -> Có thể có khoảng trắng trước/sau dấu ngoặc mở
# (['"])             -> Dấu nháy đơn hoặc kép (Group 1)
# (.*?)              -> Nội dung bên trong (Group 2 - lấy nội dung cần dịch)
# \1                 -> Đóng đúng loại dấu nháy đã mở ở Group 1
PATTERN = re.compile(r"(?:__|trans|@lang)\s*\(\s*(['\"])(.*?)\1")

def main():
    translations = {}
    
    # 1. Đọc file vi.json cũ (nếu có) để giữ lại các từ đã dịch
    if os.path.exists(OUTPUT_FILE):
        try:
            with open(OUTPUT_FILE, 'r', encoding='utf-8') as f:
                translations = json.load(f)
            print(f"✅ Đã tải {len(translations)} từ vựng cũ từ {OUTPUT_FILE}")
        except Exception as e:
            print(f"⚠️ Lỗi đọc file cũ: {e}")

    found_count = 0
    
    # 2. Duyệt qua các thư mục
    print("🚀 Đang quét project...")
    for root_dir in SCAN_DIRS:
        for root, dirs, files in os.walk(root_dir):
            for file in files:
                if file.endswith(EXTENSIONS):
                    file_path = os.path.join(root, file)
                    
                    try:
                        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                            content = f.read()
                            
                            # Tìm tất cả các khớp trong file
                            matches = PATTERN.findall(content)
                            
                            for quote_type, text in matches:
                                # Nếu từ này chưa có trong file json thì thêm vào
                                if text not in translations:
                                    translations[text] = text # Mặc định để tiếng Anh, sau này bạn vào sửa
                                    found_count += 1
                                    # print(f"   + Tìm thấy: {text}") # Bật dòng này nếu muốn xem chi tiết
                                    
                    except Exception as e:
                        print(f"❌ Lỗi đọc file {file_path}: {e}")

    # 3. Sắp xếp lại danh sách theo bảng chữ cái cho dễ tìm
    sorted_translations = {k: translations[k] for k in sorted(translations)}

    # 4. Lưu lại file
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        json.dump(sorted_translations, f, ensure_ascii=False, indent=4)

    print("-" * 30)
    print(f"🎉 Hoàn tất! Đã thêm mới {found_count} từ.")
    print(f"📂 Tổng cộng: {len(sorted_translations)} từ trong file {OUTPUT_FILE}")
    print("👉 Hãy mở file lang/vi.json và bắt đầu dịch!")

if __name__ == "__main__":
    main()