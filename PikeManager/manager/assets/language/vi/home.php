<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page_root'      => 'Danh sách',
        'title_page_directory' => 'Danh sách: ',

        'directory_empty'    => 'Thư mục trống',
        'checkbox_all_entry' => 'Chọn tất cả',

        'alert' => [
            'tips'                           => 'Chế độ phát triển đang bật, các thư mục tập tin của app được hiển thị với màu nhạt hơn. Hãy cẩn thận khi chế độ này được bật, trong quá trình sử dụng có thể thao tác sai lên thư mục và tập tin của app.',
            'features_is_construct'          => 'Tính năng đang trong quá trình xây dựng',
            'path_not_exists'                => 'Đường dẫn không tồn tại',
            'path_not_receiver_list'         => 'Không thể lấy danh sách tập tin',
            'path_not_permission'            => 'Đường dẫn <strong>"{$path}"</strong> bạn truy cập thuộc của ứng dụng nên không thể truy cập',
            'path_not_is_file'               => 'Đường dẫn này không phải một tập tin',
            'password_user_is_equal_default' => 'Bạn chưa thay đổi mật khẩu mặc định, điều này rất nguy hiểm hãy thay đổi ngay. Thông báo sẽ xuất hiện <strong>{$time}</strong> lần trong mỗi lần đăng nhập',
            'secret_is_empty'                => 'Bạn chưa cập nhật câu hỏi bí mật hoặc câu trả lời bí mật, hãy cập nhật để có thể lấy lại mật khẩu trong trường hợp không nhớ. Thông báo này sẽ tắt khi nào bạn cập nhật câu hỏi bí mật và câu trả lời'
        ],

        'action_multi' => [
            'rename' => 'Đổi tên',
            'copy'   => 'Sao chép',
            'delete' => 'Xóa',
            'zip'    => 'Nén zip',
            'chmod'  => 'Phân quyền'
        ],

        'menu_action' => [
            'create' => 'Tạo mới',
            'upload' => 'Tải lên',
            'import' => 'Nhập khẩu'
        ],

        'sidebar' => [
            'nav' => [
                'file'     => 'Tập tin',
                'database' => 'Cơ sở dữ liệu'
            ]
        ]
    ];

?>
