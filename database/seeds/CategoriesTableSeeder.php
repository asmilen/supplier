<?php

use Illuminate\Database\Seeder;

use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'id' => 3001,
                'code' => 'MBO',
                'name' => 'Mainboard',
            ],
            [
                'id' => 3002,
                'code' => 'GCA',
                'name' => 'Card màn hình',
            ],
            [
                'id' => 3003,
                'code' => 'PCC',
                'name' => 'Case PC',
            ],
            [
                'id' => 3004,
                'code' => 'PSU',
                'name' => 'Nguồn PSU',
            ],
            [
                'id' => 3005,
                'code' => 'HDD',
                'name' => 'Ổ cứng',
            ],
            [
                'id' => 3006,
                'code' => 'RAM',
                'name' => 'Ram',
            ],
            [
                'id' => 3007,
                'code' => 'COO',
                'name' => 'Tản nhiệt, Làm mát',
            ],
            [
                'id' => 3008,
                'code' => 'CPU',
                'name' => 'CPU',
            ],
            [
                'id' => 3009,
                'code' => 'CHI',
                'name' => 'Chip - Vi xử lý',
            ],
            [
                'id' => 3010,
                'code' => 'MOU',
                'name' => 'Chuột',
            ],
            [
                'id' => 3011,
                'code' => 'SPE',
                'name' => 'Loa',
            ],
            [
                'id' => 3012,
                'code' => 'HSE',
                'name' => 'Tai nghe',
            ],
            [
                'id' => 3013,
                'code' => 'KCA',
                'name' => 'Keycap',
            ],
            [
                'id' => 3014,
                'code' => 'MON',
                'name' => 'Màn hình',
            ],
            [
                'id' => 3015,
                'code' => 'GPA',
                'name' => 'Tay cầm',
            ],
            [
                'id' => 3016,
                'code' => 'MPA',
                'name' => 'Bàn di chuột',
            ],
            [
                'id' => 3017,
                'code' => 'KEY',
                'name' => 'Bàn phím',
            ],
            [
                'id' => 3018,
                'code' => 'USB',
                'name' => 'USB',
            ],
            [
                'id' => 3019,
                'code' => 'UPS',
                'name' => 'Bộ lưu điện UPS',
            ],
            [
                'id' => 3020,
                'code' => 'PRI',
                'name' => 'Máy in',
            ],
            [
                'id' => 3021,
                'code' => 'LAP',
                'name' => 'Laptop',
            ],
            [
                'id' => 3022,
                'code' => 'DES',
                'name' => 'Máy tính bàn',
            ],
            [
                'id' => 3023,
                'code' => 'SER',
                'name' => 'Máy chủ Server',
            ],
            [
                'id' => 3024,
                'code' => 'TAB',
                'name' => 'Máy tính bảng',
            ],
            [
                'id' => 3025,
                'code' => 'MOB',
                'name' => 'Điện thoại',
            ],
            [
                'id' => 3026,
                'code' => 'NSP',
                'name' => 'Bộ chia mạng',
            ],
            [
                'id' => 3027,
                'code' => 'WTR',
                'name' => 'Bộ phát Wifi',
            ],
            [
                'id' => 3028,
                'code' => 'CAM',
                'name' => 'Camera',
            ],
            [
                'id' => 3029,
                'code' => 'NCA',
                'name' => 'Card mạng',
            ],
            [
                'id' => 3030,
                'code' => 'NEJ',
                'name' => 'Đầu mạng',
            ],
            [
                'id' => 3031,
                'code' => 'CAB',
                'name' => 'Dây mạng',
            ],
            [
                'id' => 3032,
                'code' => 'IPA',
                'name' => 'Gói cước Internet',
            ],
            [
                'id' => 3033,
                'code' => 'MOD',
                'name' => 'Modem',
            ],
            [
                'id' => 3034,
                'code' => 'LBA',
                'name' => 'Thiết bị cân bằng tải',
            ],
            [
                'id' => 3035,
                'code' => 'RAC',
                'name' => 'Tủ server',
            ],
            [
                'id' => 3036,
                'code' => 'BAG',
                'name' => 'Bàn ghế',
            ],
            [
                'id' => 3037,
                'code' => 'CKM',
                'name' => 'Combo Phím Chuột',
            ],
            [
                'id' => 3038,
                'code' => 'HOL',
                'name' => 'Bao da',
            ],
            [
                'id' => 3039,
                'code' => 'BCH',
                'name' => 'Sạc dự phòng',
            ],
            [
                'id' => 3040,
                'code' => 'MES',
                'name' => 'Thẻ nhớ',
            ],
            [
                'id' => 3041,
                'code' => 'TVB',
                'name' => 'TV Box',
            ],
            [
                'id' => 3042,
                'code' => 'REC',
                'name' => 'Đầu thu',
            ],
            [
                'id' => 3043,
                'code' => 'OTH',
                'name' => 'Khác',
            ],
            [
                'id' => 3044,
                'code' => 'CRE',
                'name' => 'Combo Đầu thu',
            ],
        ];

        foreach ($categories as $category) {
            Category::forceCreate([
                'id' => $category['id'],
                'code' => $category['code'] ? : $category['id'],
                'name' => $category['name'],
                'status' => true,
            ]);
        }
    }
}
