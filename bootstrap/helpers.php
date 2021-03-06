<?php

use Carbon\Carbon;

if (! function_exists('convert_time')) {
    function convert_time($time)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $time, 'UTC')
            ->setTimezone('Asia/Ho_Chi_Minh');
    }
}
