<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */

/**
 * language class.
 */
class lang {

    public $_fields = array(
        "id",
        "name",
        "slug",
        "active",
        "locale",
        "priority",
        "customerLanguage",
        "tld"
    );

    public $strings;
    public $table = 'lang';
    public $table_strings = 'lang_strings';


    /**
     * @param $_id
     * @return mixed
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

            $strings = $db->query("SELECT *
                FROM " . $this->table_strings . "
                WHERE language = '" . $this->id . "'");

            $resultLang = $strings->fetchAll();

            if ($resultLang) {
                foreach ($resultLang as $string) {
                    if (!isset($this->strings[$string['label']]))
                        $this->strings[$string['label']] = utf8_encode($string['text']);
                }
            }
            

            return $this;

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

    }


    /**
     * @return $this|bool|lang|mixed
     */
    public function getSiteLanguage() {

        if (strstr($_SERVER['HTTP_HOST'], ".com") !== false) {
            // .com language selects language from browser
            $return = $this->getLanguageFromCookie();
            if (!$return) {
                $language_slug = $this->getBrowserLanguage();
                $this->getFromSlug($language_slug, true);

                if (empty($this->slug))
                    $this->getFromId(LANG);

                $return = $this;

            }
        } else {
            $return = $this->getLanguageFromDomain();
        }

        return $return;
    }

    /**
     * @return string
     */
    public function getBrowserLanguage() {
        return substr(@$_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    }


    /**
     * @return bool|mixed
     */
    public function getLanguageFromCookie() {
        $return = false;
        if (!empty($_COOKIE[COOKIE_NAME . "_lang"])) {
            $language = $_COOKIE[COOKIE_NAME . "_lang"];
            $return = $this->getFromId($language);
        }

        return $return;
    }


    /**
     * @return $this
     */
    public function getLanguageFromDomain() {

        $tld = explode('.', DOMAIN_NAME);
        
        $this->getFromTld($tld[1]);
        $this->redirectToMainDomain();

        return $this;
    }


    /**
     *
     */
    public function redirectToMainDomain() {

        header("Location: ".HTTP_MODE."www." . DOMAIN_NAME ."/?lang=" . $this->slug);
        exit;
    }


    /**
     *
     */
    public function setCookie() {

        if (!empty($this->id)) {
            setcookie(COOKIE_NAME . "_lang",
                      $this->id,
                      time()+60*60*24*7,
                      COOKIE_PATH,
                      COOKIE_DOMAIN);
        }

    }


    /**
     * @param $_slug
     * @param bool $_short
     * @return int|lang
     */
    public function getFromSlug($_slug, $_short = false) {

        $db = new DB;

        $return = LANG;

        $slug_where = 'slug';
        if ($_short)
            $slug_where = 'SUBSTR(slug, 1, 2)';

        $_slug = substr($_slug, 0, 3);
        $language = $db->query("SELECT id FROM " . $this->table . " WHERE $slug_where = '$_slug' AND active = 1");
        if (@$language) {
            $this->getFromId($language->fetchColumn(0));
            $return = $this;
        }
        return $return;
    }


    /**
     * @param $_tld
     * @return int|lang
     */
    public function getFromTld($_tld) {
        $db = new DB;

        $return = LANG;
        $language = $db->query("SELECT id FROM " . $this->table . " WHERE tld = '$_tld'");
        if (@$language) {
            $this->getFromId($language->fetchColumn(0));
            $return = $this;
        }

        return $return;
    }


    /**
     * @return array
     */
    public function getLanguages() {
        $db = new DB;

        $return = array();

        $items = $db->query("SELECT * FROM " . $this->table . " ORDER BY id ASC");

        foreach ($items->fetchAll() as $l) {
            $ob_lang = new lang;
            $return[] = $ob_lang->getFromId($l['id']);
        }

        return $return;
    }


    /**
     * @param bool $_all
     * @param bool $_byPriority
     * @return array
     */
    public function getList($_all = false, $_byPriority = false) {
        $db = new DB;

        $return = array();
        $result = null;

        $where = '';
        $order = '';

        if ($_byPriority)
            $order = 'ORDER BY priority ASC';

        if ($_all)
            $where = "WHERE active = 1";

        $items = $db->query("SELECT id FROM " . $this->table . " $where $order");
        
        if (@$items){
            $result = $items->fetchAll();
            foreach ($result as $item) {
                $ob_lang = new lang;
                $return[] = $ob_lang->getFromId($item['id']);
            }
        }


        return @$return;
    }





    /**
     * @param $_label
     * @param string $_utf8
     * @return string
     */
    public function _($_label, $_utf8 = '') {

        //$return = $this->getString($_label);
        $return = @$this->strings[$_label];
        switch ($_utf8) {
            case('d'):
                $return = utf8_decode($return);
                break;
            case('e'):
                $return = utf8_encode($return);
                break;
        }

        if (empty($return)) $return = "#$_label#";

        return $return;
    }


    /**
     * @param $_label
     * @return string
     */
    public function l($_label) {
        return $this->_($_label, '');
    }


    /**
     * @param $_item
     */
    public function setItem($_item) {
        foreach ($this->_fields as $field) {
            if (isset($_item[$field]))
                $this->$field = $_item[$field];
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

}

?>