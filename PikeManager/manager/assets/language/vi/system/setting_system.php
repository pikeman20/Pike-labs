<?php

    return [
        'title_page' => 'Cài đặt hệ thống',

        'form' => [
            'input' => [
                'login_max_lock_count'          => 'Số lần đăng nhập sai sẽ bị khóa:',
                'login_time_lock'               => 'Thời gian khóa (tính bằng giây):',
                'login_time_login'              => 'Thời gian một phiên đăng nhập (tính bằng giây):',
                'cache_lifetime'                => 'Thời gian lưu cache (tính bằng giây):',
                'tmp_lifetime'                  => 'Thời gian lưu tập tin tạm (tính bằng giây):',
                'tmp_limit'                     => 'Số lượng tập tin tạm được tạo ra:',
                'check_update_time'             => 'Thời gian kiểm tra cập nhật tự động (tính bằng giây):',

                'enable_disable_label'          => 'Bật/Tắt',
                'enable_forgot_password'        => 'Lấy lại mật khẩu',
                'enable_lock_count_failed'      => 'Khóa khi đăng nhập sai quá số lần',
                'enable_captcha_secure'         => 'Mã bảo vệ khi đăng nhập',
                'enable_check_password_default' => 'Kiểm tra mật khẩu có là mặc định và cảnh báo',
                'enable_development'            => 'Chế độ phát triển',

                'check_update_enable'           => 'Tự động kiểm tra cập nhật (nên bật)'
            ],

            'button' => [
                'save'   => 'Lưu lại',
                'cancel' => 'Quay lại'
            ],

            'placeholder' => [
                'input_login_max_lock_count' => 'Nhập số lần đăng nhập sai sẽ khóa',
                'input_login_time_lock'      => 'Nhập thời gian khóa',
                'input_login_time_login'     => 'Nhập thời gian cho một phiên đăng nhập',
                'input_cache_lifetime'       => 'Nhập thời gian lưu cache',
                'input_tmp_lifetime'         => 'Nhập thời gian lưu tập tin tạm',
                'input_tmp_limit'            => 'Nhập giới hạn số lượng tập tin được tạo ra',
                'input_check_update_time'    => 'Nhập thời gian tự động kiểm tra cập nhật'
            ]
        ],

        'alert' => [
            'tips'                          => 'Một phiên đăng nhập sẽ chết sau khoảng thời gian cài đặt nếu không được sử dụng. ' .
                                               'Số lượng tập tin tạm được tạo ra nếu vượt quá số lượng những tập tin được tạo ra trước đó sẽ bị xóa để tạo tập tin mới. ' .
                                               'Chế độ phát triển chỉ sử dụng khi cần thiết nếu không hãy tắt đi',

            'login_max_lock_count_is_small' => 'Số lần sai sẽ bị khóa phải lớn hơn hoặc bằng <strong>{$count}</strong>',
            'login_max_lock_count_is_large' => 'Số lần sai sẽ bị khóa phải nhỏ hơn hoặc bằng <strong>{$count}</strong>',
            'login_time_lock_is_small'      => 'Thời gian khóa phải lớn hơn hoặc bằng <strong>{$time}</strong> giây',
            'login_time_lock_is_large'      => 'Thời gian khóa phải nhỏ hơn hoặc bằng <strong>{$time}</strong> giây',
            'login_time_login_is_small'     => 'Thời gian cho một phiên đăng nhập phải lớn hơn hoặc bằng <strong>{$time}</strong> giây',
            'cache_lifetime_is_small'       => 'Thời gian lưu cache phải lớn hơn hoặc bằng <strong>{$time}</strong> giây',
            'cache_lifetime_is_large'       => 'Thời gian lưu cache phải nhỏ hơn hoặc bằng <strong>{$time}</strong> giây',
            'tmp_lifetime_is_small'         => 'Thời gian lưu tập tin tạm phải lớn hơn hoặc bằng <strong>{$time}</strong> giây',
            'tmp_lifetime_is_large'         => 'Thời gian lưu tập tin tạm phải nhỏ hơn hoặc bằng <strong>{$time}</strong> giây',
            'tmp_limit_is_small'            => 'Số lượng tập tin tạm được tạo ra phải lớn hơn hoặc bằng <strong>{$count}</strong>',
            'tmp_limit_is_large'            => 'Số lượng tập tin tạm được tạo ra phải nhỏ hơn hoặc bằng <strong>{$count}</strong>',
            'check_update_time_is_small'    => 'Thời gian tự động kiểm tra cập nhật phải lớn hơn hoặc bằng <strong>{$time}</strong> giây',
            'check_update_time_is_large'    => 'Thời gian tự động kiểm tra cập nhật phải nhỏ hơn hoặc bằng <strong>{$time}</strong> giây',
            'save_setting_failed'           => 'Lưu cài đặt thất bại',
            'save_setting_success'          => 'Lưu cài đặt thành công'
        ]
    ];
