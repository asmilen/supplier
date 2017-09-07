<?php

return [
    'product' => [
        'state' => [
            0 => 'Hết hàng',
            1 => 'Còn Hàng',
            2 => 'Đặt Hàng',
        ],
        'status' => [
            0 => 'Chờ duyệt',
            1 => 'Hết hàng',
            2 => 'Ưu tiên lấy hàng',
            3 => 'Yêu cầu ưu tiên lấy hàng',
            4 => 'Không ưu tiên lấy hàng'
        ],
        'channel' => [
            1 => 'Online',
            2 => 'Offline',
            3 => 'tekshop',
        ],

    ],

    'stores' => [
        1 => 'Online',
        2 => 'Offline',
        3 => 'Phòng máy',
    ],

    'regions' => [
        1 => 'Miền Bắc',
        2 => 'Miền Trung',
        3 => 'Miền Nam',
    ],

    'bundleLabels' => [
        1 => 'Máy chủ',
        2 => 'Máy trạm',
        3 => 'Linh kiện, phụ kiện khác',
    ],

    'manager_levels' => [
        1 => 'Khu vực (Miền)',
        2 => 'Tỉnh',
    ],

    'manager_emails' => [
        'to' => [
            'tuan.nm1@teko.vn',
            'nhuong.nx@teko.vn',
            'dung.nd@teko.vn',
            'khoa.nha@teko.vn',
            'nguyen.tp@teko.vn',
        ],
        'cc' => [
            'tuan.na@teko.vn',
            'dung.cc@teko.vn',
            'hiep.pn@teko.vn',
        ],
    ],

    'supplier' => [
        'sup_type' => [
            1 => 'Hàng mua',
            2 => 'Ký gửi',
        ],
    ],
];
