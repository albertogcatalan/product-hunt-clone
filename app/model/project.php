<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */

class project extends base_object
{
    public $_fields = array(
        "id",
        "type",
        "user",
        "slug",
        "url",
        "added",
        "started",
        "active",
        "cancelled",
    );

    // Multi-language
    public $_language_fields = array(
        'name',
        'description',
        'project',
        'language',
    );

    public $author;
    public $points;

    public $posted; //format date

    public $table = 'project';
    public $table_languages = 'project_strings';
    public $table_points = 'project_points';

    # Internal
    public $_language;

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

            $strings = $db->query("SELECT project, language, name, description
                    FROM " . $this->table_languages . "
                    WHERE project = '" . $this->id . "'");

            $resultLang = $strings->fetchAll();

            if ($resultLang) {
                foreach ($resultLang as $item) {
                    $this->name[$item['language']] = $item['name'];
                    $this->description[$item['language']] = $item['description'];
                }
            }

            $obj_u = new user;
            $this->author = $obj_u->getFromId($this->user);

            $this->_language = LANG;
            $this->getPoints();

            $this->posted = $this->relativeTime(strtotime($this->started));

            return $this;

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

    }

    public function getUserVotes(){

        $return = NULL;
        $_id = NULL;

        try {
            $db = new DB;

            $return = array();

            $_id = $this->id;

            $hUser = new user; #Helper
        
            $items = $db->query("SELECT DISTINCT u.id
                        FROM ".$hUser->table." u
                        JOIN  ". $this->table_points . " p ON ( p.user = u.id ) 
                        WHERE p.project = '$_id'
                        ORDER BY u.id");

            if ($items){
                foreach ($items->fetchAll() as $item) {
                    $obj_u = new user;
                    $return[] = $obj_u->getFromId($item['id']);
                }
            }

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

        return $return;
    }

    /**
     * getFromSlug function
     * @param type $_slug 
     * @return type
     */
    public function getFromSlug($_slug)
    {
        $return = NULL;

        try {
            $db = new DB;

            $item = $db->query("SELECT id FROM " . $this->table . " WHERE slug = '$_slug' LIMIT 1");
            $result = $item->fetchColumn(0);

            if ($result) {
                $return = $this->getFromId($result);
            }

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

        return $return;
        
    }

    /**
     * getLastProjects function.
     * 
     * @access public
     * @return void
     */
    public function getLastProjects($_date) {
        
        $return = NULL;

        try {
            $db = new DB;

            $return = array();
        
            $items = $db->query("SELECT id 
                FROM " . $this->table . " 
                WHERE active = 1 AND DATE(started) = '" . $_date . "' ORDER BY started DESC");


            if ($items){
                foreach ($items->fetchAll() as $item) {
                    $obj_p = new project;
                    $return[] = $obj_p->getFromId($item['id']);
                }
            }

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

        return $return;
    }
    
    /**
     * relativeTime
     * @param type $dt 
     * @param type $precision 
     * @return type
     */
    public function relativeTime($dt,$precision=1)
    {
        $times=array(   24*60*60        => "d",
                        60*60           => "h",
                        60              => "min",
                        1               => "s");
        
        $passed=time()-$dt;
        
        if($passed<5)
        {
            if ($passed==0){
                $output='1 s';
            } else {
                $output=$passed.' s';
            }
            
        }
        else
        {
            $output=array();
            $exit=0;
            
            foreach($times as $period=>$name)
            {
                if($exit>=$precision || ($exit>0 && $period<60)) break;
                
                $result = floor($passed/$period);
                if($result>0)
                {
                    $output[]=$result.' '.$name.($result==1?'':'');
                    $passed-=$result*$period;
                    $exit++;
                }
                else if($exit>0) $exit++;
            }
                    
            $output=implode(' and ',$output).'';
        }
        
        return $output;
    }

    /**
     * start function.
     * 
     * @access public
     * @return void
     */
    public function start() {
        if (!empty($this->id) and $this->started == 0) {
            $this->started = date('Y-m-d', time());
            $this->save();
        }
    }

    public function actionPoint($_pid, $_uid, $status){
        
        try {

            $db = new DB;
            if ($status){
                $db->query("DELETE FROM ". $this->table_points . " WHERE user = '$_uid' AND project = '$_pid'");
            } else {
                $db->query("INSERT INTO ". $this->table_points . " (project, user) VALUES ('$_pid', '$_uid')");
            }
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    
        return $status;
    }


    /**
     * save function.
     * 
     * @access public
     * @return void
     */
    public function save()
    {

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

            $this->saveLanguages();

            return true;

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

    }

    /**
     * saveLanguages function.
     * 
     * @access public
     * @return void
     */
    public function saveLanguages()
    {
        try {

            $db = new DB;
            $obj_Lang = new lang;

            $languages = $obj_Lang->getLanguages();
            foreach ($languages as $lang) {
                $lid = $lang->id;
                $text = array();
                $text['project'] = $this->id;
                $text['language'] = $lid;
                $text['name'] = $this->name[$lid];
                $text['description'] = $this->description[$lid];

                $check = $db->query("SELECT id
                    FROM " . $this->table_languages . "
                    WHERE project = '" . $this->id . "'
                        AND language = '" . $lid . "'");

                $set = '';
                $values = array();

                foreach ($this->_language_fields as $field) {
                    if ($set != '') $set .= ', ';
                    $set .= "$field = :$field";
                    $values[$lid][":$field"] = $this->$field;
                }

                $values[$lid][":name"] = $values[$lid][":name"][$lid];
                $values[$lid][":description"] = $values[$lid][":description"][$lid];
                $values[$lid][":project"] = $this->id;
                $values[$lid][":language"] = $lid;

                $idLangProject = $check->fetchColumn(0);

                if (!empty($idLangProject)) { // Already have languages
                    $values[$lid][":id"] = $idLangProject;
                    
                    $sql = "UPDATE " . $this->table_languages . " SET " . $set . " WHERE id = :id";
                } else {
                    $sql = "INSERT INTO " . $this->table_languages . " SET " . $set;
                }

                $db->query($sql, $values[$lid]);

               
            }

            return true;

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
            
        
    }

 

    
    public function isActive()
    {
        $return = ($this->active == 1 || $this->active == 0);
        return $return;
    }

    /**
     * name function.
     * 
     * @access public
     * @return void
     */
    public function name()
    {
        return $this->name[$this->_language];
    }

    /**
     * description function.
     * 
     * @access public
     * @return void
     */
    public function description()
    {
        return $this->description[$this->_language];
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

        //Multilanguage
        $this->name = $_item['name'];
        $this->description = $_item['description'];
        /*$obj_Lang = new lang;
        $languages = $obj_Lang->getLanguages();
        foreach ($languages as $lang) {
            $lid = $lang->id;
            $this->name[$lid] = $_item['name'][$lid];
            $this->description[$lid] = $_item['description'][$lid];
        }*/

    }


    /**
     * @return array
     */
    public function getItem() {
        $item = array();
        foreach ($this->_fields as $field) {
            $item[$field] = $this->$field;
        }

        foreach ($this->_language_fields as $field) {
            $item[$field] = $this->$field;
        }
        return $item;
    }
    
    /**
     * isOwnProject function.
     * 
     * @access public
     * @return void
     */
    public function isOwnProject()
    {
        global $_user;

        return ($this->user == $_user->id);
    }


    /**
     * getUrl function.
     * 
     * @access public
     * @param bool $_hash (default: true)
     * @return void
     */
    public function getUrl($_hash = true)
    {
        if (!empty($this->id)) return '/project/' . $this->slug . '/comments/';
    }

    /**
     * URL function.
     * 
     * @access public
     * @param string $_section (default: 'summary')
     * @return void
     */
    public function URL($_section = 'comments') {
        if (!empty($this->id))
            return '/project/' . $this->slug . "/" . $_section . "/";
    }
   
  
	public function getPoints(){
	    
        try {
            $db = new DB;

            $_id = $this->id;

            $item = $db->query("SELECT DISTINCT count(id) as count
            FROM ". $this->table_points ."
            WHERE project = '$_id'");

            $result = $item->fetchColumn(0);

            if ($result){
                $this->points = $result;       
                return true;
            } else {
                $this->points = 0;
                return false;
            }
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }
	
	

}

?>