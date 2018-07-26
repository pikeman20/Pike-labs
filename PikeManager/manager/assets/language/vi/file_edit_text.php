<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page'    => 'Sửa văn bản',
        'title_page_as' => 'Sửa dạng văn bản',

        'form' => [
            'input' => [
                'content_file' => 'Nội dung tập tin:',
                'options'      => 'Tùy chọn',
                'syntax_check' => 'Kiểm tra lỗi cú pháp'
            ],

            'button' => [
                'save'   => 'Lưu lại',
                'cancel' => 'Quay lại'
            ]
        ],

        'alert' => [
            'tips'                         => 'Nếu bạn sửa 1 tập tin .htaccess nó sẽ được mặc định kiểm tự động được kiểm tra lỗi trước khi lưu, để đảm bảo không xảy ra lỗi',
            'file_is_not_format_text_edit' => 'Tập tin không phải định dạng văn bản có thể sửa được',
            'htaccess_check_error_code'    => 'Tập tin <strong>.htaccess</strong> có lỗi, kết quả: <strong>{$code}</strong>',
            'function_exec_is_disable'     => 'Không thể chạy chức năng kiểm tra lỗi cú pháp do function: exec, shell_exec bị chặn',
            'not_check_syntax'             => 'Không thể kiểm tra lỗi cú pháp',
            'not_support_check_syntax'     => 'Host của bạn không hỗ trợ kiểm tra lỗi cú pháp php, nên tính năng đó không thể hiển thị',
            'syntax_not_error'             => 'Không có lỗi cú pháp',
            'save_text_success'            => 'Lưu văn bản thành công',
            'save_text_failed'             => 'Lưu văn bản thất bại',
        ]
    ];

?>
