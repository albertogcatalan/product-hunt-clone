<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */

class rc4 {
    static function encrypt($_string, $_key ) {
        $s = array();
        for ($i = 0; $i < 256; $i++) {
            $s[$i] = $i;
        }
        $j = 0;
        for ($i = 0; $i < 256; $i++) {
            $j = ($j + $s[$i] + ord($_key[$i % strlen($_key)])) % 256;
            $x = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $x;
        }
        $i = 0;
        $j = 0;
        $ct = '';
        for ($y = 0; $y < strlen($_string); $y++) {
            $i = ($i + 1) % 256;
            $j = ($j + $s[$i]) % 256;
            $x = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $x;
            $ct .= $_string[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
        }
        return $ct;
    }

    static function decrypt($_string, $_key) {
        return self::encrypt($_string, $_key);
    }

    static function d($_string, $_key) {
        return self::decrypt($_string, $_key);
    }

    static function e($_string, $_key) {
        return self::encrypt($_string, $_key);
    }

}
?>