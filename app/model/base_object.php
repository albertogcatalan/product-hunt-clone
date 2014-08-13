<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */

class base_object extends core {

    public $id;

    /**
     *
     */
    public function __construct(){

    }

    /**
     * @param $_id
     */
    public function getFromId($_id) {

        try {

            $db = new DB;
            $_id = (int)$_id;

            $item = $db->query("SELECT * FROM " . $this->table . " WHERE id = '$_id'");

            $result = $item->fetchAll();

            if ($result) {
                $this->setItem($result[0]);
            }

            return $this;

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

    }

    /**
     * @return bool
     */
    public function save() {

        try {

            $db = new DB;

            $set = '';
            $values = array();

            foreach ($this->_fields as $field) {
                if ($set != '') $set .= ', ';
                $set .= "$field = :$field";
                $values[":$field"] = $this->$field;
            }

            if(empty($this->id)){
                $sql = "INSERT INTO " . $this->table . " SET " . $set;

            } else {
                $values[':id'] = $this->id;
                $sql = "UPDATE " . $this->table . " SET " . $set . " WHERE id = :id";
            }

            $db->query($sql, $values);

            if(empty($this->id)){
                $this->id = $db->insertId();
            }

            return true;

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }


    }


    /**
     * @return bool
     */
    public function delete() {
        $db = new DB;

        $_id = (int)$this->id;

        $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
        if ($db->query($sql, array(':id'=>$_id))) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param $_item
     */
    public function setItem($_item) {
        foreach ($this->_fields as $field) {
            if (isset($_item[$field])){
                $this->$field = $_item[$field];
            }
        }
    }


    /**
     * @return array
     */
    public function getItem() {
        $item = array();
        foreach ($this->_fields as $field) {
            $item[$field] = $this->$field;
        }
        return $item;
    }


    /**
     * @return bool
     */
    public function toArray() {
        $return = false;

        if (!empty($this->_fields)) {
            foreach ($this->_fields as $field) {
                $return[$field] = $this->$field;
            }
        }

        if (!empty($this->_fields_extra)) {
            foreach ($this->_fields_extra as $field) {
                $return[$field] = $this->$field;
            }
        }

        return $return;
    }


    /**
     * @return string
     */
    public function toJson() {
        $return = $this->toArray();

        return json_encode($return);
    }


    /**
     * format function
     * @param $_format
     * @return $this|bool|string
     */
    public function format($_format) {
        switch ($_format) {
            case('json'):
                return $this->toJson();
                break;
            case('array'):
                return $this->toArray();
                break;
           default:
                return $this;
                break;
        }
    }

    /**
     * cleanString function
     * @param type $_str 
     * @return type
     */
    public static function cleanString($_str)
    {
        // Change characteres...
        $i = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í',
            'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß',
            'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï',
            'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă',
            'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē',
            'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ',
            'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ',
            'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń',
            'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ',
            'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť',
            'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ',
            'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ',
            'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ',
            'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', '!', '?', '\\', '.', '&', ',', ':', '(', ')', ';', '^', '¡', '¿', '//', '"', '@');
        // ...in this other...
        $o = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I',
            'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's',
            'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i',
            'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A',
            'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E',
            'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G',
            'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ',
            'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N',
            'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r',
            'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't',
            'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w',
            'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A',
            'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A',
            'a', 'AE', 'ae', 'O', 'o', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        $str = str_replace($i, $o, $_str);
        // Replace more
        return strtolower(preg_replace(array('/[^a-zA-Z0-9 -_\/]/', '/[ -]+/', '/[ _]+/', '/[ \/]+/', '/^-|-$/'), array('', '-', '_', '/', ''), $str));
    }


}

?>