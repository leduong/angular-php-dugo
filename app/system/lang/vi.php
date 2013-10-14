<?php

// Error names
$lang = array(
	E_ERROR				=> 'Error',
	E_WARNING			=> 'Warning',
	E_PARSE				=> 'Parsing Error',
	E_NOTICE			=> 'Notice',
	E_CORE_ERROR		=> 'Core Error',
	E_CORE_WARNING		=> 'Core Warning',
	E_COMPILE_ERROR		=> 'Compile Error',
	E_COMPILE_WARNING	=> 'Compile Warning',
	E_USER_ERROR		=> 'User Error',
	E_USER_WARNING		=> 'User Warning',
	E_USER_NOTICE		=> 'User Notice',
	E_STRICT			=> 'Runtime Notice',
	//E_RECOVERABLE_ERROR => 'Recoverable Error',	PHP 5.2.0
	//E_DEPRECATED		=> 'Deprecated Code',		PHP 5.3.0
	//E_USER_DEPRECATED	=> 'Deprecated Code',		PHP 5.3.0
);


/*
 * Cookie key
 */
$lang['cookie_no_key'] = 'Bạn phải thiết lập một khóa cookie trong tập tin cấu hình';


/*
 * Form Validation
 */
$lang['validation_no_rules'] = 'Các trường %s không có một quy tắc (%s)';
$lang['validation_rule_not_found'] = 'Không tìm thấy quy tắc cho %s.';
$lang['validation_set']= 'Các lĩnh vực %s phải nhập.';
$lang['validation_required'] = '%s bắt buộc và không thể trống.';

$lang['validation_alpha'] = '%s chỉ nhập các ký tự chữ cái.';
$lang['validation_alpha_numeric'] = '%s chỉ nhập các ký tự chữ cái và chữ số.';
$lang['validation_numeric'] = '%s chỉ nhập chữ số.';
$lang['validation_min_length'] = '%s tối thiếu ít nhất %s ký tự.';
$lang['validation_max_length'] = '%s tối đa nhiều nhất %s ký tự.';
$lang['validation_exact_length'] = 'The %s field must be exactly %s characters in length.';
$lang['validation_valid_email'] = '%s phải là địa chỉ email hợp lệ.';
$lang['validation_valid_base64'] = 'The %s field must contian valid Base 64 characters.';
$lang['validation_matches'] = '%s và %s không hợp lệ.';
$lang['validation_invalid_token'] = 'Phiên mã không hợp lệ. Hãy thử lại.';


/*
 * HTML class
 */
$lang['pagination_previous'] = '‹ <span class="hidden-480">trước</span>';
$lang['pagination_last'] = '<span class="hidden-480">cuối</span> »';
$lang['pagination_first'] = '« <span class="hidden-480">đầu</span>';
$lang['pagination_next'] = '<span class="hidden-480">sau</span> ›';

// For data/time form element
$lang['html_months'] = array(1=>'1','2','3','4','5','6','7','8','9','10','11','12');
$lang['html_datetime'] = 'Ngày%5$s Tháng%1$s Năm%2$s'; // Month/Day/Year @ Hour:Minute

/*
 * Time class
 */
$lang['time_units'] = array('year'=>31557600,'month'=>2635200,'week'=>604800,'day'=>86400,'hour'=>3600,'minute'=>60,'second'=>1);

return $lang;