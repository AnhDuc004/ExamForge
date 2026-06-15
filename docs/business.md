Luồng dữ liệu trong ExamForge

Bước 1 — Khởi tạo tenant và user
tenants → users → roles → permissionsa
                ↘ user_roles → role_permissions

Đầu tiên một tenant được tạo (ví dụ: Trường ĐH ABC). Sau đó admin tạo users trong tenant đó. Mỗi user được gán roles qua bảng user_roles. Mỗi role có danh sách quyền qua role_permissions. Từ đây hệ thống biết ai được làm gì — tạo đề, chấm bài, hay chỉ được làm bài.

Bước 2 — Xây question bank
users (creator) → questions

Người tạo đề tạo các questions trong question bank của tenant mình. Ban đầu câu hỏi ở trạng thái draft, sau khi hoàn thiện chuyển sang published thì mới dùng được cho đề thi. Câu hỏi có thể tái sử dụng ở nhiều đề khác nhau.

Bước 3 — Xây và xuất bản đề thi
users (creator) → tests → test_sections → test_section_questions ← questions
                                                      ↓
                                              question_snapshot (chụp lại nội dung)

Creator tạo một test, chia thành nhiều test_sections (phần 1: nghe, phần 2: đọc hiểu...). Mỗi section chứa các test_section_questions — đây là bảng nối câu hỏi vào đề. Quan trọng: lúc này hệ thống chụp lại toàn bộ nội dung câu hỏi vào question_snapshot. Từ đây trở đi dù câu hỏi gốc bị sửa hay xóa, đề thi vẫn giữ nguyên nội dung lúc xuất bản.

Bước 4 — Phân công bài thi
tests → assignments ← users (assignee)
                    ← users (assigned_by)

Creator tạo một assignment — bản ghi giao đề cho một thí sinh cụ thể. Assignment chứa: ai được làm, hạn nộp bao giờ, làm tối đa mấy lần, cách truy cập (bằng tài khoản hay token). Chỉ user được assign mới vào được bài thi đó.

Bước 5 — Thí sinh làm bài
assignments → attempts → answers
                  ↓
            expires_at = started_at + duration_seconds

Khi thí sinh bắt đầu, hệ thống tạo một attempt và tính ngay expires_at ở backend. Trong lúc làm, mỗi câu trả lời được lưu vào answers (gắn với test_section_question_id để biết trả lời câu nào). Khi nộp bài hoặc hết giờ, attempt chuyển sang submitted và bị khóa — không sửa được nữa.

Bước 6 — Chấm điểm
attempts → answers
               ↓
        auto_score (MCQ — tính ngay khi nộp)
        manual_score (essay — chờ reviewer)
               ↓
        review_status: pending → reviewed
               ↓
        attempts.is_finalized = true → total_score, is_passed

Ngay khi nộp bài, hệ thống tự chấm các câu MCQ và ghi auto_score vào từng answer. Các câu essay/short answer để review_status = pending. Reviewer vào xem từng câu, điền manual_score và reviewer_feedback, chuyển status thành reviewed. Khi tất cả answers đã reviewed, attempt được finalize — tính total_score, so với passing_score để set is_passed, rồi public kết quả.

Bước 7 — Ghi nhật ký song song
mọi hành động quan trọng → audit_logs

Xuyên suốt tất cả các bước trên, mọi hành động đáng kể đều ghi vào audit_logs: tạo câu hỏi, xuất bản đề, phân công, bắt đầu làm bài, nộp bài, chấm điểm, finalize kết quả. Bảng này không tham gia vào luồng xử lý — nó chỉ ghi lại để tra cứu, debug, và kiểm toán sau này.

Tóm tắt luồng một câu lệnh
tenant
  └── user (creator) → question → test → test_section → test_section_question (snapshot)
  └── user (creator) → assignment → attempt → answer → auto/manual score → finalized
  └── user (reviewer)                        ↑ chấm thủ công ở đây
  └── audit_logs ← ghi lại mọi bước



DB ExamForge
 : LINK
