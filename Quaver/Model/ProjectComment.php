<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

namespace Quaver\Model;
use Quaver\Core\DB;

/**
 * Class ProjectComment
 */
class ProjectComment extends Base
{

    public $_fields = array(
        "id",
        "project",
        "user",
        "language",
        "replyTo",
        "comment",
        "date"
    );

    # Helpers
    public  $_author;
    public  $_replies;

    # Tables
    public  $table = 'project_comments';

    /**
     * getFromId function.
     * 
     * @access public
     * @param mixed $_id
     * @return void
     */
    public function getFromId($_id)
    {
        try {

            $db = new DB;
            $_id = (int)$_id;

            $item = $db->query("SELECT * FROM " . $this->table . " WHERE id = '$_id'");

            $result = $item->fetchAll();

            if ($result) {
                $this->setItem($result[0]);
            
                $obj_user = new User;
                $this->_author = $obj_user->getFromId($this->user);
                
                $this->getReplies();

            }

            return $this;

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

    }

    /**
     * getReplies function.
     * 
     * @access public
     * @return void
     */
    public function getReplies()
    {
        if ($this->id > 0) {
            $this->_replies = $this->getFromComment($this->id);
        }
    }

    /**
     * getFromComment function
     * @param type $_id 
     * @return type
     */
    public function getFromComment($_id)
    {
        
        $return = null;

        try {

            $db = new DB;
            $result = null;

            $items = $db->query("SELECT id
                FROM " . $this->table . "
                WHERE replyTo = '$_id'
                ORDER BY date ASC");

            $result = $items->fetchAll();

            if ($result) {
                $return = array();
                foreach ($result as $item) {
                    $obC = new ProjectComment;
                    $return[] = $obC->getFromId($item['id']);
                }
            }

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

        return $return;
    }

    
    public function getFromProject($_id, $_num = 20)
    {
        $return = null;
        try {

            $db = new DB;

            $items = $db->query("SELECT id
                FROM " . $this->table . "
                WHERE project = '$_id'
                    AND replyTo = '0'
                ORDER BY date DESC LIMIT $_num");

            $result = $items->fetchAll();

            if ($result) {
                $return = array();
                foreach ($result as $item) {
                    $obC = new ProjectComment;
                    $return[] = $obC->getFromId($item['id']);
                }
            }

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
        return $return;
    }

    /**
     * comment function.
     * 
     * @access public
     * @return void
     */
    public function comment()
    {
        # Decode HTML entities and THEN encode into UTF-8 IF IS OLD COMMENT
    	     
	    return html_entity_decode($this->comment);    
        
    }

}
?>
