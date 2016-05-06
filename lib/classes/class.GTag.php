<?php
/**
 * @file class.GTag.php
 * @brief Contiene la definizione ed implementazione della classe Gino.GTag
 *
 * @copyright 2014-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use \Gino\App\SysClass\ModuleApp;
use \Gino\App\Module\ModuleInstance;

/**
 * @brief Classe per il trattamento di campi di tipo tag
 *
 * @copyright 2014-2016 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class GTag {

    private static $_table_tag = 'sys_tag',
                   $_table_tag_taggeditem = 'sys_tag_taggeditem';

    /**
     * @brief Salva i tag su db, sia nella tabella tag che nella tabella di associazione ai contenuti
     * 
     * @param string $content_controller_class nome della classe controller del modello cui i tag sono associati
     * @param string $content_controller_instance id dell'istanza della classe controller del modello cui i tag sono associati
     * @param string $content_class la classe del modello cui i tag sono associati
     * @param int $content_id l'id del oggetto cui i tag sono associati
     * @param string $tags stringa di tag separati da virgole
     * @return TRUE
     */
    public static function saveContentTags($content_controller_class, $content_controller_instance, $content_class, $content_id, $tags) {
        
    	$db = Db::instance();
        // delete all content_class/content_id associated tags
        $db->delete(self::$_table_tag_taggeditem, "content_controller_class='".$content_controller_class."' AND content_class='".$content_class."' AND content_id='".$content_id."'");
        // insert new tags
        $cleaned_tags = array_map('trim', explode(',', $tags));
        foreach($cleaned_tags as $tag) {
            if($tag != '') {
                $rows = $db->select('id', self::$_table_tag, "tag='".$tag."'");
                if($rows and count($rows)) {
                    $tag_id = $rows[0]['id'];
                }
                else {
                    $db->insert(array('tag' => $tag), self::$_table_tag);
                    $tag_id = $db->getlastid(self::$_table_tag);
                }
                $db->insert(array(
                    'content_controller_class' => $content_controller_class,
                    'content_controller_instance' => $content_controller_instance,
                    'content_class' => $content_class,
                    'content_id' => $content_id,
                    'tag_id' => $tag_id
                ), self::$_table_tag_taggeditem);
            }
        }
        return TRUE;
    }

    /**
     * @brief Ritorna un array di tag associati al contenuto dato
     * 
     * @param string $content_controller_class nome della classe controller del modello cui i tag sono associati
     * @param string $content_class la classe del modello cui i tag sono associati
     * @param int $content_id l'id del oggetto cui i tag sono associati
     * @return array di tag
     */
    public static function getContentTags($content_controller_class, $content_class, $content_id) {
        
    	$res = array();
        $db = Db::instance();
        $rows = $db->select('tag_id', self::$_table_tag_taggeditem, "content_controller_class='".$content_controller_class."' AND content_class='".$content_class."' AND content_id='".$content_id."'");
        $tags_id = array();
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $tags_id[] = $row['tag_id'];
            }
        }

        if(count($tags_id)) {
            $rows = $db->select('tag', self::$_table_tag, "id IN (".implode(',', $tags_id).")");
            if($rows and count($rows)) {
                foreach($rows as $row) {
                    $res[] = $row['tag'];
                }
            }
        }

        return $res;
    }

    /**
     * @brief Array di tutti i tag presenti nel sistema
     * @return array di tag
     */
    public static function getAllTags() {
        
    	$res = array();
        $db = Db::instance();
        $rows = $db->select('tag', self::$_table_tag, '', array('order' => 'tag'));
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $res[] = $row['tag'];
            }
        }

        return $res;
    }

    /**
     * @brief Fornisce contenuti correlati basandosi su corrispondenza di tag
     * 
     * @param string $content_controller_class nome della classe controller del modello cui i tag sono associati
     * @param string $content_class la classe dell'oggetto per il quale cercare contenuti correlati
     * @param string $content_id la id dell'oggetto per il quale cercare contenuti correlati
     * @return contenuti correlati
     */
    public static function getRelatedContents($content_controller_class, $content_class, $content_id) {

        Loader::import('sysClass', 'ModuleApp');
        Loader::import('module', 'ModuleInstance');

        $res = array();
        $db = Db::instance();
        $where = "tag_id IN (SELECT tag_id FROM ".self::$_table_tag_taggeditem." 
        		WHERE content_controller_class='".$content_controller_class."' AND content_class='".$content_class."' AND content_id='".$content_id."') 
        		AND NOT (content_controller_class='".$content_controller_class."' AND content_class='".$content_class."' AND content_id='".$content_id."')";
        
        $rows = $db->select(
        	'*, COUNT(content_id) AS freq', 
        	self::$_table_tag_taggeditem, 
        	$where, 
        	array(
        		'group_by' => 'content_controller_class, content_class, content_id', 
        		'order' => 'content_controller_class, content_class, freq DESC, content_id DESC'
        ));
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $controller_name = $row['content_controller_class'];
                $controller_instance = $row['content_controller_instance'];
                $content_class = $row['content_class'];
                $content_id = $row['content_id'];
                $freq = $row['freq'];
                // load the content class
                Loader::import($controller_name, $content_class);
                if($controller_instance) {
                    $module = new ModuleInstance($controller_instance);
                    if($module->active) {
                        if(!isset($res[$module->label])) {
                            $res[$module->label] = array();
                        }
                        $class = get_model_app_name_class_ns($controller_name, $content_class);
                        $controller_class = get_app_name_class_ns($controller_name);
                        $object = new $class($content_id, new $controller_class($controller_instance));
                        if(method_exists($object, 'gtagOutput')) {
                            $res[$module->label][] = $object->gtagOutput();
                        }
                        elseif(method_exists($object, 'getUrl')) {
                            $res[$module->label][] = "<a href=\"".$object->getUrl()."\">".((string) $object)."</a>";
                        }
                        else {
                            $res[$module->label][] = (string) $object;
                        }
                    }
                }
                else {
                    $module_app = ModuleApp::getFromName($controller_name);
                    if($module_app->active) {
                        if(!isset($res[$module_app->label])) {
                            $res[$module_app->label] = array();
                        }
                        $class = get_model_app_name_class_ns($controller_name, $content_class);
                        $object = new $class($content_id);
                        if(method_exists($object, 'gtagOutput')) {
                            $res[$module_app->label][] = $object->gtagOutput();
                        }
                        elseif(method_exists($object, 'getUrl')) {
                            $res[$module_app->label][] = "<a href=\"".$object->getUrl()."\">".((string) $object)."</a>";
                        }
                        else {
                            $res[$module_app->label][] = (string) $object;
                        }
                    }
                }
            }
        }

        return $res;
    }

    /**
     * @brief Isrogramma dei tag (array tag->freqeunza)
     * @description Utile per la scrittura di una tag cloud
     * @return istogramma tags
     */
    public static function getTagsHistogram() {
        
    	$res = array();
        $db = Db::instance();
        
        $rows = $db->select(
        	self::$_table_tag.'.tag', 
        	array(self::$_table_tag,  self::$_table_tag_taggeditem), 
        	self::$_table_tag.'.id = '.self::$_table_tag_taggeditem.'.tag_id', 
        	array('order' => self::$_table_tag_taggeditem.'.tag_id')
        );
        if($rows and count($rows)) {
            foreach($rows as $row) {
                if(!isset($res[$row['tag']])) {
                    $res[$row['tag']] = 0;
                }
                $res[$row['tag']]++;
            }
        }

        ksort($res);

        return $res;
    }

    /**
     * Elimina le associazioni dei tag ai contenuti
     *
     * @param string $controller_class nome della classe controller del modello cui i tag sono associati
     * @param integer $controller_instance valore id dell'istanza della classe controller del modello cui i tag sono associati
     * @param string $model_class nome della classe del modello cui i tag sono associati
     * @param integer $model_id valore id dell'oggetto cui i tag sono associati
     * @return boolean
     *
     * Example in the model
     * @code
     * GTag::deleteTaggedItem($this->_controller->getClassName(), $this->_controller->getInstance(), get_name_class($this), $this->id);
     * @endcode
     */
    public static function deleteTaggedItem($controller_class, $controller_instance, $model_class, $model_id) {
    	 
    	$db = Db::instance();
    	$result = $db->delete(
    		self::$_table_tag_taggeditem,
    		"content_controller_class='$controller_class' AND content_controller_instance='$controller_instance' AND content_class='$model_class' AND content_id='$model_id'"
    	);
    	return $result;
    }
}
