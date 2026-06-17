# **TÀI LIỆU KẾ HOẠCH PHÁT TRIỂN & THIẾT KẾ API EXAMFORGE**

## **I. PHÂN QUYỀN HỆ THỐNG (ROLES & PERMISSIONS)**

1. **System Admin (Bên cung cấp dịch vụ):** Quản trị cấp cao nhất của nền tảng SaaS. Tập trung vận hành hạ tầng (health check database, queues), khởi tạo hoặc thu hồi không gian làm việc (tenant) của khách hàng. Không can thiệp vào dữ liệu nội bộ của tổ chức.  
2. **Admin (Bên thuê / Tổ chức):** Quản trị cao nhất bên trong một tổ chức (Tenant). Quản lý danh sách người dùng, cấp quyền, giám sát hoạt động qua Audit Log và xem báo cáo tổng hợp. Bị giới hạn hoàn toàn trong dữ liệu của tổ chức mình.  
3. **Creator (Người tạo đề):** Quản lý ngân hàng câu hỏi, tải lên media, đóng gói đề thi (sections, questions), xuất bản và phân công đề thi cho thí sinh.  
4. **Reviewer (Người chấm bài):** Xem bài nộp, chấm điểm thủ công các câu tự luận, ghi nhận xét và hoàn tất kết quả để hệ thống công bố.  
5. **Student (Thí sinh / Người học):** Truy cập bài thi được giao, làm bài và nộp bài trong thời gian quy định, xem điểm khi được công bố.  
6. **Guest (Khách vãng lai):** Truy cập kỳ thi qua token/link chia sẻ (không có tài khoản cố định).

## **II. KẾ HOẠCH PHÁT TRIỂN 5 GIAI ĐOẠN**

**GIAI ĐOẠN 1: Nền tảng Lõi & Định danh (Identity & Multi-tenancy)**

* **Mục tiêu:** Xây dựng móng vững chắc cho kiến trúc SaaS và phân quyền.  
* **Thiết lập kiến trúc Multi-tenant:** Hệ thống hỗ trợ nhiều tenant. Dữ liệu của các tenant phải được cô lập ở mọi API và truy vấn dữ liệu.  
* **Quản lý Danh tính (Identity):** Quản lý các user, vai trò và quyền của từng nhóm vận hành.  
* **Hệ thống Phân quyền (RBAC):** Kiểm soát quyền truy cập riêng cho nhóm tạo đề, nhóm chấm bài và người làm bài dựa trên Roles và Permissions.

**GIAI ĐOẠN 2: Công cụ Xây dựng Đề thi (Question Bank & Test Builder)**

* **Mục tiêu:** Hoàn thiện luồng tạo và đóng gói nội dung thi.  
* **Quản lý Ngân hàng Câu hỏi:** Tạo, sửa, lưu trữ, phân loại các dạng câu hỏi (một lựa chọn, nhiều lựa chọn, trả lời ngắn, tự luận).  
* **Ràng buộc Dữ liệu:** Câu hỏi đã lưu trữ không được dùng cho đề thi mới để đảm bảo tính toàn vẹn.  
* **Tạo và Xuất bản Đề thi:** Xây dựng đề từ nhiều phần thi, sắp xếp vị trí câu hỏi, cấu hình thời gian và điểm đạt.  
* **Tính Bất biến của Đề thi:** Khi đề đã xuất bản, tạo snapshot để nội dung dùng cho các lần làm bài hiện hữu không bị thay đổi ngoài ý muốn.

**GIAI ĐOẠN 3: Động cơ Thi cử (Assignment & Attempt Engine)**

* **Mục tiêu:** Đưa đề thi đến tay người học và xử lý luồng làm bài an toàn.  
* **Phân công Bài thi:** Gán đề thi cho thí sinh, thiết lập hạn nộp, số lần làm tối đa và cách truy cập.  
* **Bảo mật Truy cập:** Cấp token/link truy cập an toàn, cô lập nội dung và kết quả theo phân quyền.  
* **Trải nghiệm Làm bài:** Điều hướng câu hỏi, hiển thị bộ đếm thời gian, lưu tự động (auto-save).  
* **Bảo vệ Tính Toàn vẹn:** Thời gian làm bài và tiến trình nộp bài phải được quản lý và khóa chặt bởi trạng thái backend, tránh gian lận từ phía frontend.

**GIAI ĐOẠN 4: Chấm điểm & Báo cáo (Grading & Reporting)**

* **Mục tiêu:** Xử lý kết quả, minh bạch quy trình chấm và cung cấp báo cáo.  
* **Chấm tự động & Thủ công:** Tự động chấm câu hỏi khách quan. Đưa câu hỏi tự luận vào hàng chờ review thủ công để chấm và phản hồi.  
* **Báo cáo Kết quả:** Thống kê kết quả thi, chi tiết bài làm, đánh giá đạt/không đạt.  
* **Audit Log:** Ghi nhận lịch sử thao tác quan trọng (tạo câu hỏi, xuất bản đề, thu bài, sửa điểm).

**GIAI ĐOẠN 5: Tối ưu hóa & Tiện ích Nâng cao (Performance & AI)**

* **Mục tiêu:** Đảm bảo hiệu năng mở rộng và tích hợp tiện ích thông minh.  
* **Tối ưu Hiệu năng:** Phân trang dữ liệu, lazy loading, tối ưu truy vấn để không tải toàn bộ dữ liệu khi không cần thiết.  
* **Vận hành & Giám sát:** Ghi log lỗi, theo dõi trạng thái hệ thống và tiến trình chấm điểm.  
* **Tích hợp AI:** Hỗ trợ tạo nháp câu hỏi, gợi ý độ khó/nhãn, hoặc gợi ý phản hồi chấm thi (luôn qua bước con người duyệt cuối cùng).

## **III. THIẾT KẾ API CHI TIẾT (API BLUEPRINT)**

**🌐 Các Tiêu chuẩn Chung (Global Standards)**

* **Base URL:** Tất cả endpoint dùng prefix /api/v1.  
* **Authentication:** Sử dụng Bearer Token.  
* **Pagination & Filtering:** Hỗ trợ query string (?page=1\&per\_page=20\&status=published).

### **GIAI ĐOẠN 1: Nền tảng Lõi & Định danh**

| Method | Endpoint | Mô tả & Chức năng | Phân quyền (Role) |
| :---- | :---- | :---- | :---- |
| **POST** | /api/v1/auth/login | Xác thực thông tin, trả về Access Token. | All |
| **POST** | /api/v1/auth/logout | Revoke (thu hồi) token hiện tại. | All |
| **GET** | /api/v1/auth/me | Lấy profile, tenant context và mảng quyền (permissions). | All |
| **GET** | /api/v1/users | Lấy danh sách users kèm bộ lọc tìm kiếm và phân trang. | Admin |
| **POST** | /api/v1/users | Tạo user mới và gán Role (VD: Creator, Reviewer). | Admin |
| **PUT** | /api/v1/users/{id}/status | Active/Deactive một tài khoản (Soft Delete). | Admin |
| **GET** | /api/v1/roles | Liệt kê các Role hiện có trong tenant. | Admin |

### **GIAI ĐOẠN 2: Xây dựng Đề thi & Quản lý Media**

**1\. Quản lý Media**

| Method | Endpoint | Mô tả & Chức năng | Phân quyền |
| :---- | :---- | :---- | :---- |
| **POST** | /api/v1/media/presigned-url | Xin URL upload tạm thời lên cloud. | Creator |
| **POST** | /api/v1/media | Đăng ký file đã upload vào database (trả về file ID). | Creator |

**2\. Ngân hàng Câu hỏi**

| Method | Endpoint | Mô tả & Chức năng | Phân quyền |
| :---- | :---- | :---- | :---- |
| **GET** | /api/v1/questions | Liệt kê câu hỏi. Hỗ trợ lọc theo loại câu hỏi, tags. | Creator |
| **POST** | /api/v1/questions | Tạo câu hỏi mới (lưu options/correct\_answers dạng JSONB). | Creator |
| **POST** | /api/v1/questions/bulk-import | *\[Job\]* Upload file Excel/CSV danh sách câu hỏi. | Creator |
| **PUT** | /api/v1/questions/bulk-update | Cập nhật tags, độ khó hàng loạt. | Creator |

**3\. Đóng gói Đề thi**

| Method | Endpoint | Mô tả & Chức năng | Phân quyền |
| :---- | :---- | :---- | :---- |
| **GET** | /api/v1/tests | Danh sách đề thi (lọc draft, published). | Creator |
| **POST** | /api/v1/tests | Tạo khung đề thi (tiêu đề, mô tả, duration). | Creator |
| **PUT** | /api/v1/tests/{id}/sections | Thêm, sửa, xóa phần thi, nhúng câu hỏi và cập nhật vị trí. | Creator |
| **POST** | /api/v1/tests/{id}/publish | Đóng băng đề thi (tạo snapshot câu hỏi). | Creator |

### **GIAI ĐOẠN 3: Động cơ Thi cử & Chống Gian Lận**

**1\. Phân công**

| Method | Endpoint | Mô tả & Chức năng | Phân quyền |
| :---- | :---- | :---- | :---- |
| **POST** | /api/v1/assignments | Gán đề thi cho học sinh (cài đặt max\_attempts, due\_at). | Creator |
| **GET** | /api/v1/assignments/my | Liệt kê các bài tập/kỳ thi chờ xử lý của thí sinh. | Student |
| **GET** | /api/v1/assignments/verify | Xác thực token/link truy cập tạm thời. | Student / Guest |

**2\. Luồng Làm bài**

| Method | Endpoint | Mô tả & Chức năng | Phân quyền |
| :---- | :---- | :---- | :---- |
| **POST** | /api/v1/attempts/{assign\_id}/start | Bắt đầu. Backend chốt mốc expires\_at. | Student |
| **GET** | /api/v1/attempts/{id} | Lấy cấu trúc bài làm (ẩn đáp án đúng). | Student |
| **POST** | /api/v1/attempts/{id}/heartbeat | Đồng bộ thời gian thực tế với server. | Student |
| **PATCH** | /api/v1/attempts/{id}/answers | Lưu tự động (Auto-save). | Student |
| **POST** | /api/v1/attempts/{id}/resume | Phục hồi state và câu trả lời khi rớt mạng/F5. | Student |
| **POST** | /api/v1/attempts/{id}/submit | Chủ động nộp bài. Tự đóng nếu quá hạn. | Student |
| **POST** | /api/v1/attempts/{id}/force-submit | Thu bài ép buộc do vi phạm nội quy. | Admin / Reviewer |

### 

### **GIAI ĐOẠN 4: Chấm điểm & Trích xuất Dữ liệu**

| Method | Endpoint | Mô tả & Chức năng | Phân quyền |
| :---- | :---- | :---- | :---- |
| **GET** | /api/v1/grading/pending | Danh sách bài chờ chấm (hiển thị trạng thái grading). | Reviewer |
| **PUT** | /api/v1/answers/{id}/review | Cập nhật manual\_score và Feedback cho câu hỏi. | Reviewer |
| **POST** | /api/v1/grading/attempts/{id}/finalize | Chốt sổ (cộng tổng điểm, chuyển trạng thái finalized). | Reviewer |
| **GET** | /api/v1/reports/tests/{id} | Lấy bảng điểm tổng kết của một kỳ thi. | Admin / Reviewer |
| **POST** | /api/v1/reports/tests/{id}/export | *\[Job\]* Đẩy tác vụ xuất file (Excel/PDF) vào Queue. | Admin |
| **GET** | /api/v1/jobs/{job\_id}/download | Lấy URL tải file khi tác vụ Export hoàn tất. | Admin |

### **GIAI ĐOẠN 5: Tối ưu hóa, AI & Vận hành**

| Method | Endpoint | Mô tả & Chức năng | Phân quyền |
| :---- | :---- | :---- | :---- |
| **GET** | /api/v1/audit-logs | Truy vết thao tác (actor, action, target\_id, ip). | Admin |
| **POST** | /api/v1/ai/generate-questions | *\[Job\]* Sinh câu hỏi nháp từ prompt (cần duyệt). | Creator |
| **POST** | /api/v1/ai/suggest-feedback | Gợi ý nhận xét bài tự luận của thí sinh. | Reviewer |
| **GET** | /api/v1/health | Ping kiểm tra kết nối Database, ổ cứng, Redis Queue. | System Admin |

**III. Bản thiết kế Cơ sở dữ liệu**  
https://dbdiagram.io/d/6a2ec1705c789b8acb7d8ddc

**IV. Tiêu chuẩn Giao tiếp API (API Response Format)**

Để đảm bảo tính nhất quán, toàn bộ các API của ExamForge (từ luồng xác thực, làm bài thi đến tải file) đều phải tuân thủ nghiêm ngặt các quy chuẩn định dạng JSON dưới đây.

### **1\. Quy ước chung (General Rules)**

* **Content-Type:** Luôn là `application/json` (ngoại trừ các API liên quan đến tải file vật lý).  
* **Trạng thái HTTP (HTTP Status Codes):** Sử dụng đúng mã trạng thái để phía Client bắt sự kiện dễ dàng mà không cần đọc ruột JSON.  
  * **200 OK:** Truy vấn thành công (GET, PUT, PATCH).  
  * **201 Created:** Tạo mới tài nguyên thành công (POST).  
  * **400 Bad Request:** Lỗi logic từ phía client (ví dụ: nộp bài khi đã hết giờ).  
  * **401 Unauthorized:** Thiếu token hoặc token đã hết hạn.  
  * **403 Forbidden:** Có token nhưng không đủ quyền truy cập (Role/Permission bị từ chối).  
  * **404 Not Found:** Không tìm thấy tài nguyên (ID sai hoặc bị xóa).  
  * **422 Unprocessable Entity:** Lỗi validate dữ liệu đầu vào.  
  * **500 Internal Server Error:** Lỗi nghiêm trọng từ phía server (cần ghi log).

### **2\. Định dạng Thành công (Success Response)**

Áp dụng cho các request trả về dữ liệu đơn (Chi tiết câu hỏi, Thông tin User) hoặc thao tác thành công (Xóa, Cập nhật).

JSON  
{  
  "success": true,  
  "message": "Lấy thông tin đề thi thành công.",  
  "data": {  
    "id": "uuid-1234",  
    "title": "Đề thi Giữa kỳ",  
    "status": "published"  
  }  
}

### **3\. Định dạng Danh sách có Phân trang (Pagination Response)**

Áp dụng cho mọi API có tính chất liệt kê (Danh sách câu hỏi, Danh sách bài nộp, Audit Logs) nhằm tránh quá tải payload. Cấu trúc `meta` hỗ trợ Frontend làm UI phân trang (Pagination Component).

JSON  
{  
  "success": true,  
  "message": "Lấy danh sách câu hỏi thành công.",  
  "data": \[  
    {  
      "id": "uuid-1",  
      "content": "Thủ đô của Việt Nam là gì?"  
    },  
    {  
      "id": "uuid-2",  
      "content": "1 \+ 1 \= ?"  
    }  
  \],  
  "meta": {  
    "current\_page": 1,  
    "per\_page": 20,  
    "total": 150,  
    "last\_page": 8  
  }  
}

### **4\. Định dạng Lỗi chung (Error Response)**

Áp dụng cho các lỗi 400, 401, 403, 404 hoặc 500\. Message phải ngắn gọn, trực diện để UI có thể lấy ra hiển thị thành Toast/Snackbar thông báo cho người dùng.

JSON  
{  
  "success": false,  
  "message": "Bài thi đã hết hạn, không thể nộp thêm.",  
  "error\_code": "ATTEMPT\_EXPIRED",  
  "data": null  
}

Ghi chú: `error_code` (string) là tùy chọn dùng để đánh dấu các mã lỗi nghiệp vụ cụ thể giúp Mobile/Web dễ dàng handle đa ngôn ngữ (i18n) thay vì parse text.

### **5\. Định dạng Lỗi Xác thực Dữ liệu (Validation Error \- HTTP 422\)**

Luồng quan trọng nhất khi Submit Form. Cấu trúc lỗi sẽ gom nhóm theo từng key của input để React/Flutter dễ dàng map text lỗi vào ngay dưới ô nhập liệu tương ứng.

JSON  
{  
  "success": false,  
  "message": "Dữ liệu đầu vào không hợp lệ.",  
  "errors": {  
    "email": \[  
      "Email này đã tồn tại trong hệ thống.",  
      "Email không đúng định dạng."  
    \],  
    "duration\_seconds": \[  
      "Thời gian làm bài phải lớn hơn 0."  
    \]  
  }  
}

**V. KIẾN TRÚC HẠ TẦNG & MÔI TRƯỜNG**

### **1\. Môi trường Phát triển Local (Local Environment)**

Để đảm bảo sự đồng bộ tuyệt đối giữa máy của các Developer và môi trường chạy thực tế, dự án sử dụng **Docker** và **Docker Compose**. Mô hình này giả lập chính xác luồng đi của request qua Nginx trước khi vào application.

* **Web Server:** Nginx (Alpine).  
* **Application:** PHP-FPM (Laravel).  
* **Database:** MySQL 8.0+.  
* **Cache & Queue:** Redis 7\.  
* **Object Storage:** MinIO (giả lập Cloud Storage để test upload).

**File `docker-compose.yml` tiêu chuẩn:**

version: '3.8'

services:  
  \# Nginx Web Server & Reverse Proxy  
  webserver:  
    image: nginx:alpine  
    ports:  
      \- "80:80"  
    volumes:  
      \- ./nginx/default.conf:/etc/nginx/conf.d/default.conf  
      \- ./src:/var/www/html  
    depends\_on:  
      \- app  
    networks:  
      \- examforge-net

  \# Laravel PHP-FPM Application  
  app:  
    build:   
      context: .  
      dockerfile: Dockerfile  
    volumes:  
      \- ./src:/var/www/html  
    environment:  
      \# Database Config  
      DB\_CONNECTION: mysql  
      DB\_HOST: db  
      DB\_DATABASE: examforge\_local  
        
      \# Redis Config  
      REDIS\_HOST: redis  
        
      \# Cloudflare R2 Config (Tương thích chuẩn S3)  
      FILESYSTEM\_DISK: s3  
      AWS\_ACCESS\_KEY\_ID: ${R2\_ACCESS\_KEY\_ID}  
      AWS\_SECRET\_ACCESS\_KEY: ${R2\_SECRET\_ACCESS\_KEY}  
      AWS\_DEFAULT\_REGION: auto  
      AWS\_BUCKET: ${R2\_BUCKET\_NAME}  
      AWS\_ENDPOINT: https://${R2\_ACCOUNT\_ID}.r2.cloudflarestorage.com  
      AWS\_USE\_PATH\_STYLE\_ENDPOINT: false  
    networks:  
      \- examforge-net

  \# MySQL Database  
  db:  
    image: mysql:8.0  
    ports:  
      \- "3306:3306"  
    environment:  
      MYSQL\_DATABASE: examforge\_local  
      MYSQL\_USER: examforge\_user  
      MYSQL\_PASSWORD: secretpassword  
      MYSQL\_ROOT\_PASSWORD: rootpassword  
    volumes:  
      \- mysql\_data:/var/lib/mysql  
    networks:  
      \- examforge-net

  \# Redis Cache & Queue  
  redis:  
    image: redis:7-alpine  
    ports:  
      \- "6379:6379"  
    volumes:  
      \- redis\_data:/data  
    networks:  
      \- examforge-net

volumes:  
  mysql\_data:  
  redis\_data:

networks:  
  examforge-net:  
    driver: bridge

### **2\. Môi trường Production (SaaS Architecture)**

Để đạt được sự cân bằng giữa **độ ổn định cấp doanh nghiệp (Enterprise-grade)** và **chi phí tối ưu**, hệ thống được thiết kế theo mô hình Cloud-Native. Thay vì tự quản lý Database trên VPS dễ gặp rủi ro mất dữ liệu, chúng ta sẽ tách bạch các dịch vụ:

* **Gateway & Web Server (Nginx):** \* Đóng vai trò là "người gác cổng" (Reverse Proxy). Nginx sẽ xử lý SSL/TLS, phục vụ trực tiếp các file tĩnh (static assets) của frontend React cực kỳ nhanh chóng, và điều hướng các API requests (`/api/v1/*`) vào các container Backend đang chạy.  
* **Backend Compute (Google Cloud Run hoặc VPS Docker Swarm):**  
  * Đóng gói Laravel App thành Docker container. Nếu dùng Cloud Run, hệ thống sẽ tự động scale từ 0 lên hàng trăm instances khi có kỳ thi lớn. Các thông số bảo mật quan trọng (như `DB_PASSWORD` hay `GCP_PROJECT_ID`) sẽ được tiêm an toàn vào container dưới dạng secrets thông qua Secret Manager, tuyệt đối không hardcode trong file `.env`.  
* **Cơ sở dữ liệu (Managed Cloud SQL cho MySQL):**  
  * Sử dụng dịch vụ MySQL được quản lý (Managed Database). Hệ thống sẽ tự động lo việc backup hàng ngày, vá lỗi bảo mật (patching) và cho phép khôi phục dữ liệu theo từng phút (Point-in-time recovery). Đây là khoản đầu tư xứng đáng nhất để đảm bảo tính toàn vẹn cho điểm thi của học sinh.  
* **Lưu trữ Media (Google Cloud Storage \- GCS / AWS S3):**  
  * Các file media (hình ảnh, âm thanh) không lưu trực tiếp trên server Nginx. Client xin quyền upload (presigned-url) từ Backend và đẩy file thẳng lên Object Storage, giúp giải phóng hoàn toàn băng thông cho server chính.

### **3\. Sơ đồ Luồng Triển khai (CI/CD Pipeline)**

Quy trình đưa code lên môi trường thật được tự động hóa hoàn toàn để đảm bảo an toàn và không gây gián đoạn (Zero-downtime deployment):

1. **Push Code:** Developer đẩy code lên nhánh `main` trên GitHub/GitLab.  
2. **Build & Test:** CI/CD Runner chạy Unit Tests và Build Docker Image (chứa mã nguồn PHP mới nhất).  
3. **Push Image:** Image được đẩy lên Container Registry.  
4. **Deploy & Migrate:** Hệ thống chạy tự động lệnh `php artisan migrate --force` và cập nhật các container đang chạy. Nginx sẽ được cấu hình để không làm rớt các kết nối đang có (graceful reload).

