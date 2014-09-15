<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

namespace Quaver\Model;
use Quaver\Core\DB;
use Quaver\Model\Project;

/**
 * Class user
 */
class User extends Base
{
	 public $_fields = array(
        "id",
        "active",
        "level",
        "email",
        "name",
        "biography",
        "account",
        "avatar",
        "registered",
        "last_login",
        "newsletter",
        "language",
        "uid",
        "token",
        "secret",
        "signature",
        "location",
        "web_link",
    );

    public $cookie = '';
    public $logged = false;
    
    public $table = 'user';

    public function getPointsAdded(){
        try {
              $db = new DB;

              $return = array();
              $_hP = new Project; //helper

              $_id = $this->id;
          
              $items = $db->query("SELECT p.id 
                  FROM " . $_hP->table . " p, " . $_hP->table_points . " i
                  WHERE p.id = i.project AND p.active = 1 AND i.user = '$_id' ORDER BY p.started DESC");

              if ($items){
                  foreach ($items->fetchAll() as $item) {
                      $obj_p = new Project;
                      $return[] = $obj_p->getFromId($item['id']);
                  }
              }

          } catch (PDOException $e) {
              print "Error!: " . $e->getMessage() . "<br/>";
              die();
          }

        return $return;
    }

    public function checkPoint($_pid, $_uid) {
      try {

            $db = new DB;
            $p = new Project;

            if (empty($_uid)){
              return false;
            }

            $item = $db->query("SELECT id FROM " . $p->table_points . " WHERE user = '$_uid' AND project = '$_pid'");

            if ($item->fetchColumn(0)) {
              return true;
            } else {
              return false;
            }

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    
    }

    public function getFromAccount($_account){
    	try {

            $db = new DB;

            $item = $db->query("SELECT * FROM " . $this->table . " WHERE account = '$_account'");

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
     * isActive function.
     * 
     * @access public
     * @return void
     */
    public function isActive() 
    {
        
        return ($this->active == 1);
    }

    /**
     * isAdmin function.
     * 
     * @access public
     * @return void
     */
    public function isAdmin()
    {
        return ($this->level == "admin");
    }

    /**
     * setCookie function.
     * 
     * @access public
     * @param string $_cookie (default: '')
     * @return void
     */
    public function setCookie($_cookie = '')
    {

        if (!empty($_cookie)) $this->cookie = $_cookie;
        if (!empty($this->cookie)) {
            setCookie(COOKIE_NAME . "_log", $this->cookie, time() + 60 * 60 * 24 * 30, COOKIE_PATH, COOKIE_DOMAIN);
            
        }
    }

    /**
     * unsetCookie function.
     * 
     * @access public
     * @return void
     */
    public function unsetCookie()
    {
        setCookie(COOKIE_NAME . "_log", "", time()-1, COOKIE_PATH, COOKIE_DOMAIN);
        setCookie("PHPSESSID", "", time()-1, COOKIE_PATH);
	   
	    $this->logged = false;
  
    }

     /**
     * cookie function.
     * 
     * @access public
     * @return void
     */
    public function cookie()
    {
        if (empty($this->cookie) && !empty($this->id)) {
            $this->cookie = sha1($this->uid . md5($this->id));
        }
        return $this->cookie;
    }

    /**
    * getFromCookie function.
    * 
    * @access public
    * @param mixed $_cookie
    * @return void
    */
   public function getFromCookie($_cookie)
    {
        $db = new DB;

        $this->cookie = substr($_cookie, 0, 40);

        $id = $db->query("
            SELECT id
            FROM " . $this->table . "
                WHERE SHA1(CONCAT(uid, MD5(id))) = '" . $this->cookie . "'");

        $result = $id->fetchColumn(0);

        if ($result > 0) {
            $this->getFromId($result);
            if (!$this->isActive()){
                $this->unsetCookie();
            } else {
                $this->logged = true;
            }
           
            $return = $this->id;
            
            $this->updateLastLogin();
        } else {
            $this->unsetCookie();
            $return = false;
        }

		
		
        return $return;
    }

      /**
     * updateLastLogin function.
     * 
     * @access public
     * @return void
     */
    public function updateLastLogin() {
        if ($this->id > 0) {
            $this->last_login = time();
            $this->save();
        }
    }

    public function isRegistered($_uid)
    {
        $db = new DB;
       	
       	$id = null;

        $id = $db->query("
            SELECT id
            FROM " . $this->table . "
                WHERE uid = '" . $_uid . "'");
       
        $result = $id->fetchColumn(0);

        if ($result > 0) {
            $this->getFromId($result);
            $this->cookie();
            $this->logged = true;
            $this->updateLastLogin();
            $return = $this->id;

        } else {
            $return = false;
        }

        return $return;
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

}	
?>
