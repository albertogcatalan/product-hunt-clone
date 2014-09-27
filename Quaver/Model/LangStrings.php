<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

namespace Quaver\Model;
use Quaver\Core\DB;

/**
 * Class LangStrings
 */
class LangStrings extends Base
{
    public $_fields = array(
        "id",
        "language",
        "label",
        "text",
    );

    public $_languages;
    public $table = 'lang_strings';


    /**
     * getLanguageList function.
     * 
     * @access public
     * @return void
     */
    public function getLanguageList()
    {
        try {

            $db = new DB;
            $_id = (int)$_id;
            $_table = $this->table;
            $return = NULL;

            $item = $db->query("SELECT id FROM $_table WHERE language = 1");

            $result = $item->fetchAll();

            if ($result) {
                foreach ($result as $item) {
                    $l = new LangStrings;
                    $return[] = $l->getFromId($item['id']);
                }
            }

            return $return;

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

    }
    
    /**
     * getFromLabel function.
     * 
     * @access public
     * @param mixed $_label
     * @return void
     */
    public function getFromLabel($_label)
    {
        try {

            $db = new DB;
            $_id = (int)$_id;
            $_table = $this->table;
            $return = NULL;

            $item = $db->query("SELECT id FROM $_table WHERE label like '$_label' ORDER BY language");

            $result = $item->fetchAll();

            if ($result) {
                foreach ($result as $item) {
                    $l = new LangStrings;
                    $return[] = $l->getFromId($item['id']);
                }
            }

            return $return;

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

    }
    
   
    
    /**
     * saveAll function.
     * 
     * @access public
     * @return void
     */
    public function saveAll()
    {
        // Other languages
        if (!empty($this->_languages)) {
            foreach ($this->_languages as $item) {
                $lang = new LangStrings;
                $lang->setItem((array)$item);
				$lang->save();
            }

            return true;
        }
    }

    /**
     * deleteAll function.
     * 
     * @access public
     * @return void
     */
    public function deleteAll()
    {
        // Other languages
        if (!empty($this->_languages)) {
            foreach ($this->_languages as $item) {
                $lang = new LangStrings;
                $lang->setItem((array)$item);
                $lang->delete();
            }

            return true;
        }  
    }
    

    /**
     * @param $_item
     */
    public function setItem($_item)
    {
        foreach ($this->_fields as $field) {
            if (isset($_item[$field])){
                $this->$field = $_item[$field];
            }
        }

        if ($_item['_languages']) {
            $this->_languages = $_item['_languages'];
        }
    }
   
    

}
?>
