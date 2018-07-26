<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Đăng nhập',

        'form' => [
            'input_username_placeholder' => 'Tên đăng nhập hoặc email',
            'input_password_placeholder' => 'Mật khẩu',

            'forgot_password' => 'Quên mật khẩu',
            'button_login'    => 'Đăng nhập'
        ],

        'alert' => [
            'is_use_manager_demo'            => 'Bạn đang sử dụng phiên bản manager demo, tài khoản: <strong>admin</strong>, mật khẩu: <strong>12345</strong>',
            'lock_count_failed'              => 'Bạn đã đăng nhập sai quá <strong>{$count}</strong> lần, bạn còn <strong>{$time}</strong> nữa để đăng nhập lại',
            'unlock_count'                   => 'Bạn có thể tiếp tục đăng nhập',
            'not_input_username_or_password' => 'Chưa nhập tên đăng nhập hoặc mật khẩu',
            'not_input_captcha'              => 'Chưa nhập mã bảo vệ',
            'username_or_password_wrong'     => 'Tên đăng nhập hoặc mật khẩu sai',
            'captcha_wrong'                  => 'Mã bảo vệ sai',
            'user_not_exists'                => 'Người dùng không tồn tại',
            'login_success'                  => 'Đăng nhập thành công',
            'login_failed'                   => 'Đăng nhập thất bại',
            'login_already'                  => 'Bạn hiện đang ở trạng thái đăng nhập',
            'not_login'                      => 'Bạn chưa đăng nhập',
            'user_is_band'                   => 'Tài khoản của bạn đã bị khóa',
            'loging'                         => 'Đang đăng nhập...',
            'loging_check'                   => 'Đang kiểm tra đăng nhập...',

            'exit_session_failed'  => 'Đăng xuất thất bại',
            'exit_session_success' => 'Đăng xuất thành công'
        ]
    ];

?>