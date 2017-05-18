<?php
/**
 * Created by PhpStorm.
 * User: Namvu
 * Date: 5/11/2017
 * Time: 2:36 PM
 */

namespace App\Helpers;

use Carbon\Carbon;

class Helper
{
    public static function convertTime($time)
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $time, 'UTC');
        return $date->setTimezone('Asia/Ho_Chi_Minh');
    }
}
