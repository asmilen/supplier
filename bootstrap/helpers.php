<?php

use Carbon\Carbon;

if (! function_exists('convert_time')) {
    function convert_time($time)
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $time, 'UTC');
        return $date->setTimezone('Asia/Ho_Chi_Minh');
    }
}

if (! function_exists('generate_sku')) {
    function generate_sku($category_code, $manufacturer_code, $product_code, $color_code = '')
    {
        $sku = $category_code.'-'.$manufacturer_code.'-'.$product_code;

        if (! empty($color_code)) {
            $sku .= '-'.$color_code;
        }

        return $sku;
    }
}
