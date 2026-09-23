import os
import requests

# =========================================================
# King Saud University Press
# Book Cover Downloader
# =========================================================

OUTPUT_FOLDER = "King_Saud_University_Press"

# =========================================================
# روابط صور الكتب
# =========================================================

IMAGE_URLS = [

    "https://ksupress.ksu.edu.sa/sites/default/files/styles/product_details/public/products/%D8%BA%D9%84%D8%A7%D9%81%20%D8%B9%D8%B3%D9%80%D9%80%D8%B1%20%D8%A7%D9%84%D9%80%D8%AD%D9%80%D8%B3%D9%80%D9%80%D9%80%D8%A7%D8%A8.jpg?itok=QtSfEiTI",

    "https://ksupress.ksu.edu.sa/sites/default/files/styles/product_details/public/products/422ded76-09b5-46ee-8330-7eac9a776846.jpg?itok=OpNzXUfR",

    "https://ksupress.ksu.edu.sa/sites/default/files/styles/product_details/public/products/%D8%BA%D9%84%D8%A7%D9%81_1.jpg?itok=83AxNMIx",

    "https://ksupress.ksu.edu.sa/sites/default/files/styles/product_details/public/products/%D8%BA%D9%84%D8%A7%D9%81%20%D8%A7%D9%84%D9%81%D8%B4%D9%84%20%D8%A7%D9%84%D9%85%D8%AA%D9%83%D8%B1%D8%B1%20%D9%84%D8%A7%D9%86%D8%BA%D8%B1%D8%A7%D8%B3%20%D8%A7%D9%84%D8%A3%D8%AC%D9%86%D8%A9.jpg?itok=4xMhDDcW"
]

# =========================================================
# أسماء الكتب
# Transliteration - ليست ترجمة
# =========================================================

BOOK_NAMES = [

    "01 - Usr Al Hisab - Daniela Lucangeli",

    "02 - Al Sulook Al Tanzimi Fi Al Moassasat Al Sehiya Wa Salamat Al Mareed",

    "03 - Tameeq Al Fahm Fi Al Iطار Al Mafahimi Lil Mohasaba Al Maliya - Khaled Bin Rashid Al Adeem",

    "04 - Al Fashal Al Motakarrir Li Inghiras Al Ajinna Fi Mohawalat Atfal Al Anabeeb"
]

# =========================================================
# إنشاء المجلد
# =========================================================

os.makedirs(OUTPUT_FOLDER, exist_ok=True)

# =========================================================
# إعداد الاتصال
# =========================================================

HEADERS = {
    "User-Agent": (
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
        "AppleWebKit/537.36 "
        "(KHTML, like Gecko) "
        "Chrome/140.0 Safari/537.36"
    ),
    "Accept": (
        "image/avif,image/webp,image/apng,"
        "image/svg+xml,image/*,*/*;q=0.8"
    ),
    "Accept-Language": "en-US,en;q=0.9",
}

session = requests.Session()
session.headers.update(HEADERS)


# =========================================================
# تحديد امتداد الصورة
# =========================================================

def get_extension(response):

    content_type = response.headers.get(
        "Content-Type",
        ""
    ).lower()

    if "jpeg" in content_type or "jpg" in content_type:
        return ".jpg"

    if "png" in content_type:
        return ".png"

    if "webp" in content_type:
        return ".webp"

    if "gif" in content_type:
        return ".gif"

    return ".jpg"


# =========================================================
# تنزيل صورة
# =========================================================

def download_image(url, book_name, number):

    print()
    print("-" * 70)
    print(f"[{number}/{len(IMAGE_URLS)}] {book_name}")
    print("-" * 70)

    try:

        response = session.get(
            url,
            timeout=60,
            stream=True,
            allow_redirects=True
        )

        response.raise_for_status()

        print(f"HTTP Status : {response.status_code}")

        content_type = response.headers.get(
            "Content-Type",
            "Unknown"
        )

        print(f"Content-Type: {content_type}")

        # -------------------------------------------------
        # تحديد الامتداد الحقيقي
        # -------------------------------------------------

        extension = get_extension(response)

        # -------------------------------------------------
        # اسم الملف
        # -------------------------------------------------

        filename = f"{book_name}{extension}"

        filepath = os.path.join(
            OUTPUT_FOLDER,
            filename
        )

        # -------------------------------------------------
        # حفظ الصورة
        # -------------------------------------------------

        total_size = 0

        with open(filepath, "wb") as file:

            for chunk in response.iter_content(
                chunk_size=8192
            ):

                if chunk:

                    file.write(chunk)
                    total_size += len(chunk)

        # -------------------------------------------------
        # التحقق
        # -------------------------------------------------

        if total_size == 0:

            print("❌ الملف فارغ")

            if os.path.exists(filepath):
                os.remove(filepath)

            return False

        print("✅ تم تنزيل الصورة")
        print(f"📁 {filepath}")
        print(
            f"📦 Size: "
            f"{total_size / 1024:.2f} KB"
        )

        return True

    except requests.exceptions.HTTPError as error:

        print(f"❌ HTTP Error: {error}")
        return False

    except requests.exceptions.Timeout:

        print("❌ Timeout")
        return False

    except requests.exceptions.ConnectionError as error:

        print(f"❌ Connection Error: {error}")
        return False

    except Exception as error:

        print(f"❌ Error: {error}")
        return False


# =========================================================
# Main
# =========================================================

def main():

    print()
    print("=" * 70)
    print("       KING SAUD UNIVERSITY PRESS")
    print("             BOOK DOWNLOADER")
    print("=" * 70)

    print()
    print(f"Total Books : {len(IMAGE_URLS)}")
    print(
        f"Output      : "
        f"{os.path.abspath(OUTPUT_FOLDER)}"
    )

    # -----------------------------------------------------
    # التحقق من تطابق القوائم
    # -----------------------------------------------------

    if len(IMAGE_URLS) != len(BOOK_NAMES):

        print()
        print("❌ ERROR:")
        print(
            "عدد روابط الصور لا يساوي "
            "عدد أسماء الكتب."
        )

        return

    # -----------------------------------------------------
    # التنزيل
    # -----------------------------------------------------

    success = 0
    failed = 0

    for index, (url, book_name) in enumerate(
        zip(IMAGE_URLS, BOOK_NAMES),
        start=1
    ):

        if download_image(
            url,
            book_name,
            index
        ):

            success += 1

        else:

            failed += 1

    # =====================================================
    # التقرير النهائي
    # =====================================================

    print()
    print()
    print("=" * 70)
    print("                 DOWNLOAD COMPLETE")
    print("=" * 70)

    print()
    print(f"Total   : {len(IMAGE_URLS)}")
    print(f"Success : {success}")
    print(f"Failed  : {failed}")

    print()
    print("Output Folder:")
    print(os.path.abspath(OUTPUT_FOLDER))

    print()
    print("=" * 70)

    if failed == 0:

        print("✅ تم تنزيل جميع الصور بنجاح.")

    else:

        print(
            f"⚠️ تم تنزيل {success} "
            f"من {len(IMAGE_URLS)} صورة."
        )

    print("=" * 70)


# =========================================================
# تشغيل السكريبت
# =========================================================

if __name__ == "__main__":
    main()