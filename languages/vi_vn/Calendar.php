<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
$languageStrings = array(
    // Basic Strings
	'Calendar'=>"Lịch",
	'SINGLE_Calendar' => "Sự kiện",
	'SINGLE_Events' => "Sự kiện",
	'LBL_ADD_TASK' => "Thêm công việc",
	'LBL_ADD_EVENT' => "Thêm sự kiện",
	'LBL_RECORDS_LIST' => "Danh sách lịch",
	'LBL_EVENTS' => "Sự kiện",
	'LBL_TODOS' => "Nhiệm vụ",
	'LBL_CALENDAR_SETTINGS' => "Cài đặt lịch",
	'LBL_CALENDAR_SHARING' => "Chia sẻ lịch",
	'LBL_DEFAULT_EVENT_DURATION' => "Thời lượng sự kiện mặc định",
	'LBL_CALL' => "Gọi",
	'LBL_OTHER_EVENTS' => "Sự kiện khác",
	'LBL_MINUTES' => "Phút",
	'LBL_SELECT_USERS' => "Chọn người dùng",
	'LBL_EVENT_OR_TASK' => "Sự kiện/Nhiệm vụ",
        'LBL_DEFAULT_STATUS_TYPE' => "Trạng thái & Loại mặc định",
        'LBL_STATUS' => "Trạng thái",
        'LBL_TYPE' => "Kiểu",
	// Blocks
	'LBL_TASK_INFORMATION' => "Chi tiết nhiệm vụ",
    'LBL_EVENT_INFORMATION'=> "Chi tiết sự kiện",

	//Fields
	'Subject' => "Chủ thể",
	'Start Date & Time' => "Ngày & Giờ bắt đầu",
	'Activity Type'=>"Loại hoạt động",
	'Send Notification'=>"Gửi thông báo",
	'Location'=>"Vị trí",
	'End Date & Time' => "Ngày & Giờ Kết Thúc",
	'Visibility' => "khả năng hiển thị",
	'Recurrence' => "Lặp lại",
	
	//Visibility picklist values
	'Private' => "Riêng tư",
	'Public' => "Công cộng",
	
	//Side Bar Names
	'LBL_ACTIVITY_TYPES' => "Loại hoạt động",
	'LBL_CONTACTS_SUPPORT_END_DATE' => "Ngày kết thúc hỗ trợ",
	'LBL_CONTACTS_BIRTH_DAY' => "Ngày sinh",
	'LBL_ADDED_CALENDARS' => "Đã thêm lịch",


	//Activity Type picklist values
	'Call' => "Gọi",
	'Meeting' => "Cuộc họp",
	'Task' => "Nhiệm vụ",

	//Status picklist values
	'Planned' => "Đã lên kế hoạch",
	'Completed' => "Hoàn thành",
	'Pending Input' => "Đầu vào đang chờ xử lý",
	'Not Started' => "Chưa bắt đầu",
	'Deferred' => "Trì hoãn",
	'Held' => "Cầm",
	'Not Held' => "Không được giữ",
	
	//Priority picklist values
	'Medium' => "Trung bình",

	'LBL_CHANGE_OWNER' => "Thay đổi chủ sở hữu",

	'LBL_EVENT' => "Sự kiện",
	'LBL_TASK' => "Nhiệm vụ",
	'LBL_TASKS' => "Nhiệm vụ",

	'LBL_RECORDS_LIST' => "Xem danh sách",
	'LBL_CALENDAR_VIEW' => "Lịch của tôi",
	'LBL_SHARED_CALENDAR' => "Lịch chia sẻ",

	//Repeat Lables - used by getTranslatedString
	'LBL_DAY0' => "Chủ nhật",
	'LBL_DAY1' => "Thứ hai",
	'LBL_DAY2' => "Thứ ba",
	'LBL_DAY3' => "Thứ Tư",
	'LBL_DAY4' => "Thứ năm",
	'LBL_DAY5' => "Thứ sáu",
	'LBL_DAY6' => "Thứ bảy",

	'first' => "Đầu tiên",
	'last' => "Cuối cùng",
	'LBL_DAY_OF_THE_MONTH' => "ngày trong tháng",
	'LBL_ON' => "TRÊN",

	'Daily'=>"(Các) ngày",
	'Weekly'=>"(Các) tuần",
	'Monthly'=>"(Các) tháng",
	'Yearly'=>"Năm",
	
	//Import and Export Labels
	'LBL_IMPORT_RECORDS' => "Nhập hồ sơ",
	'LBL_RESULT' => "Kết quả",
	'LBL_FINISH' => "Hoàn thành",
	'LBL_TOTAL_TASKS_IMPORTED' => "Số tác vụ được nhập thành công",
	'LBL_TOTAL_TASKS_SKIPPED' => "Số nhiệm vụ bị bỏ qua vì thiếu một hoặc nhiều trường bắt buộc",
	'LBL_TOTAL_EVENTS_IMPORTED' => "Số sự kiện được nhập thành công",
	'LBL_TOTAL_EVENTS_SKIPPED' => "Số sự kiện bị bỏ qua vì thiếu một hoặc nhiều trường bắt buộc",
	'LBL_TOTAL_EVENTS_DUPLICATED' => "Số sự kiện trùng lặp bị bỏ qua",
	'LBL_TOTAL_TASKS_DUPLICATED' => "Số nhiệm vụ trùng lặp bị bỏ qua",
	
	'ICAL_FORMAT' => "Định dạng iCal",
	'LBL_LAST_IMPORT_UNDONE'=>"Lần nhập cuối cùng của bạn đã được hoàn tác",
	'LBL_UNDO_LAST_IMPORT' => "Hoàn tác lần nhập cuối cùng",
	
	//Fixing colors for Shared Calendar and My Calendar
	'LBL_EDIT_COLOR' => "Chỉnh sửa màu",
	'LBL_ADD_CALENDAR_VIEW' => "Thêm chế độ xem lịch",
	'LBL_SELECT_USER_CALENDAR' => "Chọn lịch người dùng",
	'LBL_SELECT_CALENDAR_COLOR' => "Chọn màu lịch",
	'LBL_EDITING_CALENDAR_VIEW' => "Chỉnh sửa chế độ xem lịch",
	'LBL_DELETE_CALENDAR' => "Xóa lịch",
	'LBL_SELECT_ACTIVITY_TYPE' => "Chọn loại hoạt động",
	'Tasks' => "Nhiệm vụ",
	'LBL_SELECT_FIELDS_FOR_RANGE' => "Chọn trường cho phạm vi",
	'LBL_DUPLICATE_VIEW_EXIST' => "Chế độ xem lịch đã tồn tại",
    
    // For Event Invitation
    'LBL_ACTIVITY_NOTIFICATION' => "Đây là thông báo cho biết một hoạt động được giao cho bạn đã được thực hiện",
    'LBL_ACTIVITY_INVITATION' => "Bạn đã được mời tham gia một hoạt động",
    'LBL_DETAILS_STRING' => "Các chi tiết là",
    'LBL_CREATED' => "tạo",
    'LBL_UPDATED' => "đã cập nhật",
    'Due Date' => "Ngày đáo hạn",
    'Priority' => "Sự ưu tiên",
    'Related To' => "Liên quan đến",
    'LBL_CONTACT_LIST' => "Danh sách liên hệ",
    'LBL_APP_DESCRIPTION' => "Sự miêu tả",
    'LBL_REGARDS_STRING' => "Cảm ơn & Trân trọng",
    'LBL_EVENT_INFORMATION' => "Chi tiết sự kiện",
	'LBL_UPDATED_INVITATION' => "Lời mời đã cập nhật",
	'LBL_INVITATION' => "Lời mời",
	
	//Recurring Events
	'LBL_EDIT_RECURRING_EVENT' => "Chỉnh sửa sự kiện định kỳ",
	'LBL_ALL_EVENTS_EDIT_INFO' => "Tất cả các sự kiện trong chuỗi sẽ được thay đổi.</br> Mọi thay đổi đối với các sự kiện khác sẽ được giữ nguyên.",
	'LBL_FUTURE_EVENTS_EDIT_INFO' => "Sự kiện này và tất cả các sự kiện sau đây sẽ bị thay đổi.</br> Mọi thay đổi đối với các sự kiện trong tương lai sẽ bị mất.",
	'LBL_ONLY_THIS_EVENT_EDIT_INFO' => "Tất cả các sự kiện khác trong chuỗi sẽ giữ nguyên.",
	'LBL_EDIT_RECURRING_EVENTS_INFO' => "Bạn có muốn lưu các thay đổi cho",
	
	'LBL_DELETE_RECURRING_EVENT' => "Xóa sự kiện định kỳ",
	'LBL_ALL_EVENTS_DELETE_INFO' => "Tất cả các sự kiện trong chuỗi sẽ bị xóa.",
	'LBL_FUTURE_EVENTS_DELETE_INFO' => "Sự kiện này và tất cả các sự kiện sau đây sẽ bị xóa.",
	'LBL_ONLY_THIS_EVENT_DELETE_INFO' => "Tất cả các sự kiện khác trong chuỗi sẽ giữ nguyên.",
	'LBL_DELETE_RECURRING_EVENTS_INFO' => "Bạn chỉ muốn xóa sự kiện này, tất cả sự kiện trong chuỗi hay sự kiện này và tất cả sự kiện trong tương lai trong chuỗi?",
	'LBL_ONLY_THIS_EVENT' => "Chỉ sự kiện này",
	'LBL_FUTURE_EVENTS' => "Sự kiện đang theo dõi",
	'LBL_ALL_EVENTS' => "Tất cả sự kiện",
	
	//Reminder Email
	'LBL_REMINDER_NOTIFICATION' => "Đây là thông báo nhắc nhở cho Hoạt động",
    'LBL_SELECT_EVENT_TYPE' => "Loại hoạt động",
    'LBL_THIS_WEEK' => "Tuần này",
    'LBL_ADD_TASK_AND_PRESS_ENTER' => "Thêm tác vụ và nhấn Enter",

	//Months
	'LBL_JANUARY' => "Tháng Một",
	'LBL_FEBRUARY' => "Tháng hai",
	'LBL_MARCH' => "Bước đều",
	'LBL_APRIL' => "Tháng tư",
	'LBL_MAY' => "Có thể",
	'LBL_JUNE' => "Tháng sáu",
	'LBL_JULY' => "Tháng bảy",
	'LBL_AUGUST' => "Tháng tám",
	'LBL_SEPTEMBER' => "Tháng 9",
	'LBL_OCTOBER' => "tháng mười",
	'LBL_NOVEMBER' => "Tháng mười một",
	'LBL_DECEMBER' => "Tháng 12",
	'LBL_CLICK_HERE_TO_VIEW' => "Bấm vào đây để xem",
);

$jsLanguageStrings = array(
	'LBL_ADD_EVENT_TASK' => "Thêm sự kiện/nhiệm vụ",
	'JS_TASK_IS_SUCCESSFULLY_ADDED_TO_YOUR_CALENDAR' => "Tác vụ đã được thêm thành công vào Lịch của bạn",
        'LBL_CANT_SELECT_CONTACT_FROM_LEADS' => "Không thể chọn Địa chỉ liên hệ có liên quan cho Khách hàng tiềm năng",
        'JS_FUTURE_EVENT_CANNOT_BE_HELD' => "Không Thể Giữ Cho Tương Lai",
	
	//Calendar view label translation
	'LBL_MONTH' => "Tháng",
	'LBL_TODAY' => "Hôm nay",
    'LBL_TOMORROW' => "Ngày mai",
	'LBL_DAY' => "Ngày",
	'LBL_WEEK' => "Tuần",
	
	'LBL_SUNDAY' => "Chủ nhật",
	'LBL_MONDAY' => "Thứ hai",
	'LBL_TUESDAY' => "Thứ ba",
	'LBL_WEDNESDAY' => "Thứ Tư",
	'LBL_THURSDAY' => "Thứ năm",
	'LBL_FRIDAY' => "Thứ sáu",
	'LBL_SATURDAY' => "Thứ bảy",
	
	'LBL_SUN' => "Mặt trời",
	'LBL_MON' => "Thứ hai",
	'LBL_TUE' => "thứ ba",
	'LBL_WED' => "Thứ tư",
	'LBL_THU' => "Thứ năm",
	'LBL_FRI' => "Thứ sáu",
	'LBL_SAT' => "Đã ngồi",
	
	'LBL_JANUARY' => "Tháng Một",
	'LBL_FEBRUARY' => "Tháng hai",
	'LBL_MARCH' => "Bước đều",
	'LBL_APRIL' => "Tháng tư",
	'LBL_MAY' => "Có thể",
	'LBL_JUNE' => "Tháng sáu",
	'LBL_JULY' => "Tháng bảy",
	'LBL_AUGUST' => "Tháng tám",
	'LBL_SEPTEMBER' => "Tháng 9",
	'LBL_OCTOBER' => "tháng mười",
	'LBL_NOVEMBER' => "Tháng mười một",
	'LBL_DECEMBER' => "Tháng 12",
	
	'LBL_JAN' => "Tháng một",
	'LBL_FEB' => "Tháng Hai",
	'LBL_MAR' => "tháng 3",
	'LBL_APR' => "tháng tư",
	'LBL_MAY' => "Có thể",
	'LBL_JUN' => "tháng sáu",
	'LBL_JUL' => "tháng 7",
	'LBL_AUG' => "tháng 8",
	'LBL_SEP' => "tháng 9",
	'LBL_OCT' => "Tháng 10",
	'LBL_NOV' => "tháng 11",
	'LBL_DEC' => "Tháng mười hai",
	'LBL_ALL_DAY' => "cả ngày",
	//End
	
	//Fixing colors for Shared Calendar and My Calendar
	'JS_CALENDAR_VIEW_COLOR_UPDATED_SUCCESSFULLY' => "Đã cập nhật màu xem lịch thành công",
	'JS_CALENDAR_VIEW_DELETE_CONFIRMATION' => "Bạn có chắc chắn muốn xóa chế độ xem Lịch này không?",
	'JS_CALENDAR_VIEW_ADDED_SUCCESSFULLY' => "Đã thêm chế độ xem lịch thành công",
	'JS_CALENDAR_VIEW_DELETED_SUCCESSFULLY' => "Đã xóa thành công Chế độ xem lịch",
	'JS_NO_CALENDAR_VIEWS_TO_ADD' => "Không có chế độ xem lịch để thêm",
	'JS_EDIT_CALENDAR' => "Chỉnh sửa lịch",
    
    //v7
    'JS_EVENT_UPDATED' => "Đã cập nhật sự kiện",
    'JS_NO_EVENTS_F0R_THE_DAY' => "Không có sự kiện nào trong ngày",
    'LBL_AGENDA' => "Chương trình nghị sự",
    'JS_CALENDAR_VIEW_YOU_ARE_EDITING_NOT_FOUND' => "Không tìm thấy chế độ xem lịch",
    
    'JS_DELETE' => "Xóa bỏ",
    'JS_EDIT' => "Biên tập",
    'JS_MARK_AS_HELD' => "Đánh dấu là đã giữ",
    'JS_CREATE_FOLLOW_UP' => "Tạo theo dõi",
    'JS_RECURRING_EVENT' => "Sự kiện định kỳ",
    'JS_DETAILS' => "Thêm&nbsp;Chi tiết",
    'JS_CHECK_START_AND_END_DATE'=>"Ngày & Giờ Kết thúc phải lớn hơn hoặc bằng Ngày & Giờ Bắt đầu",
    'JS_CHECK_START_AND_END_DATE_SHOULD_BE_GREATER'=> "Ngày và giờ kết thúc phải lớn hơn Ngày và giờ bắt đầu",
);
