<?php
/**
 * @file class.pageEntryAdminTable.php
 * Contiene la definizione ed implementazione della classe pageEntryAdminTable.
 *
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * \ingroup page
 * Classe per la gestione del backoffice delle pagine (estensione della classe adminTable del core di gino).
 *
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class pageEntryAdminTable extends adminTable {
	
	/**
	 * Metodo chiamato al salvataggio di una pagina 
	 * 
	 * @see pageTag::saveTag()
	 * @see pageEntry::saveTags()
	 * @param object $model istanza di @ref pageEntry
	 * @param array $options opzioni del form
	 * @param array $options_element opzioni dei campi
	 * @access public
	 * @return void
	 */
	public function modelAction($model, $options=array(), $options_element=array()) {

		$result = parent::modelAction($model, $options, $options_element);
		
		if(is_array($result) && isset($result['error'])) {
			return $result;
		}
		
		$session = session::instance();
		$model->author = $session->userId;
		$model->updateDbData();

		$model_tags = array();

		if($model->published) {
			foreach(explode(',', $model->tags) as $tag) {
				$tag_id = pageTag::saveTag($this->_controller->getInstance(), $tag);
				if($tag_id) {
					$model_tags[] = $tag_id;
				}
			}
		}

		return $model->saveTags($model_tags);
	}
}
?>
