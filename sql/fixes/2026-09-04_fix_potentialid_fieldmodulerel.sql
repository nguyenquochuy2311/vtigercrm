-- Fix: "Bị từ chối quyền" / "Không có quyền tạo hoặc chưa bật tạo nhanh" khi bấm
-- nút tạo nhanh (+) cạnh field "Tên cơ hội" (potentialid) trong Project, Quotes, SalesOrder.
--
-- Nguyên nhân: bảng vtiger_fieldmodulerel thiếu dòng cấu hình cho field potentialid,
-- khiến Vtiger_Field_Model::getReferenceList() trả về rỗng, input ẩn popupReferenceModule
-- không được render, JS không xác định được module tham chiếu.
--
-- Đã áp dụng trực tiếp lên DB production (newapp.khachhangtruongson.com) ngày 2026-09-04
-- bằng fieldid cứng (849/315/390) đọc được trên DB đó. Bản dưới đây tra cứu fieldid động
-- theo (module, columnname) để chạy an toàn trên môi trường khác (local/staging) mà không
-- phụ thuộc giá trị fieldid tự tăng của từng DB.

INSERT INTO vtiger_fieldmodulerel (fieldid, module, relmodule)
SELECT f.fieldid, t.name, 'Potentials'
FROM vtiger_field f
JOIN vtiger_tab t ON t.tabid = f.tabid
WHERE t.name IN ('Project', 'Quotes', 'SalesOrder')
  AND f.columnname = 'potentialid'
  AND NOT EXISTS (
      SELECT 1 FROM vtiger_fieldmodulerel r WHERE r.fieldid = f.fieldid
  );
