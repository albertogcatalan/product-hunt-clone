<?php

namespace Quaver\Model;
/**
 * Class rc4
 */
class RC4
{
    /**
     * @param $_string
     * @param $_key
     * @return string
     */
    static function encrypt($_string, $_key )
    {
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
        $strlenString = strlen($_string);

        for ($y = 0; $y < $strlenString; $y++) {
            $i = ($i + 1) % 256;
            $j = ($j + $s[$i]) % 256;
            $x = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $x;
            $ct .= $_string[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
        }
        return $ct;
    }

    /**
     * @param $_string
     * @param $_key
     * @return string
     */
    static function decrypt($_string, $_key)
    {
        return self::encrypt($_string, $_key);
    }

    /**
     * @param $_string
     * @param $_key
     * @return string
     */
    static function d($_string, $_key)
    {
        return self::decrypt($_string, $_key);
    }

    /**
     * @param $_string
     * @param $_key
     * @return string
     */
    static function e($_string, $_key)
    {
        return self::encrypt($_string, $_key);
    }

}
?>
