<?php

    return [
        'title_page' => 'Lấy lại mật khẩu',

        'form' => [
            'login'                           => 'Đăng nhập',
            'button_forgot_password'          => 'Cấp lại mật khẩu',
            'button_check_user'               => 'Kiểm tra người dùng',
            'input_username_placeholder'      => 'Nhập tên tài khoản hoặc email',
            'input_secret_answer_placeholder' => 'Nhập câu trả lời cho câu hỏi bí mật',
            'input_captcha_placeholder'       => 'Nhập mã bảo vệ'
        ],

        'alert' => [
            'forgot_password_not_enable' => 'Chức năng lấy lại mật khẩu không được bật',
            'lock_count_failed'          => 'Bạn đã nhập sai email quá <strong>{$count}</strong> lần, bạn còn <strong>{$time}</strong> nữa để nhập lại',
            'unlock_count'               => 'Bạn có thể tiếp tục',
            'not_input_username'         => 'Chưa nhập tên tài khoản hoặc email',
            'not_input_captcha'          => 'Chưa nhập mã bảo vệ',
            'not_input_secret_answer'    => 'Chưa nhập câu trả lời cho câu hỏi bí mật',
            'email_not_validate'         => 'Email không hợp lệ',
            'captcha_wrong'              => 'Mã bảo vệ sai',
            'username_wrong'             => 'Tài khoản hoặc email không tồn tại',
            'secret_answer_wrong'        => 'Câu trả lời cho câu hỏi bí mật sai, hãy thử lại',
            'user_not_exists'            => 'Người dùng không tồn tại',
            'user_is_band'               => 'Người dùng đó đã bị khóa',
            'secret_question'            => 'Bạn hãy trả lời câu hỏi bí mật: "<strong>{$secret_question}</strong>"',
            'reset_password_failed'      => 'Cấp lại mật khẩu thất bại, hãy thử lại',
            'reset_password_success'     => 'Cấp lại mật khẩu cho tài khoản <strong>{$username}</strong> thành công, mật khẩu mới <strong>{$password}</strong> hãy thay đổi mật khẩu để đảm bảo an toàn',
            'features_is_construct'      => 'Chức năng đang xây dựng'
        ],

        'mail' => [
            'name_from' => 'Hệ thống manager',
            'name_to'   => 'Người sử dụng manager',
            'subject'   => 'Yêu cấu lấy lại mật khẩu manager',
            'message'   => 'Có một yêu cầu lấy lại mật khẩu cho địa chỉ email này, nếu đó là bạn hãy nhấn vào đây để lấy lại mật khẩu'
        ]
    ];
