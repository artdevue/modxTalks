<?php

if (!function_exists('dump')) {
    function dump()
    {
        array_map(function ($x) {
            echo '<pre style="font:15px Consolas;padding:10px;border:1px solid #c2c2c2;border-radius:10px;background-color:f7f7f7;box-shadow:0 1px 2px #ccc;">';
            var_dump($x);
            echo '</pre>';
        }, func_get_args());
    }
}
