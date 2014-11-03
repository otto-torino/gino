<?php
/**
 * \file class.Permission.php
 * Contiene la definizione ed implementazione della classe Permission.
 * 
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */


/**
 * I permessi sono relativi ad una classe.
 * Nel momento in cui vengono associati a gruppi o utenti si aggiunge l'informazione istanza, che vale 0 per classi non istanziabili
 * Quando richiedo il controllo bloccante di un permesso (requirePerm) devo dare classe, codice e istanza. Il codice puo' essere un array di codici di permessi
 * tutti appartenenti alla stessa classe e istanza. Se voglio permettere l'accesso con permessi di classi o istanze diverse uso più volte il metodo
 * requirePerm.
 * I Controller hanno una shortcut di requirePerm (che è un metodo di Access) dove classe e istanza sono passati direttamente e 
 * coincidono con le proprietà del Controller stesso.
 * La stessa cosa per il controllo non bloccante, con i metodi hasPerm di User e lo shortcut userHasPerm di Controller.
 * Esistono permessi di sistema (hanno class='core') che sono generici. La classe core non esiste tra le ModuleApp, ma l'eccezione viene 
 * gestita interamente all'interno della classe Permission, per tutti gli altri si tratta di permessi analoghi agli altri e non vedono alcuna differenza.
 * Il nome della classe è core e non system siccome core è una classe esistente e non si può creare un modulo di uguale nome.
 * I Controller devono definire i permessi per le outputFunction, in quel contesto si considerano i permessi di classe, non di istanza. Servono per 
 * mostrare i permessi quando si trattano i metodi di output (menu, layout etc...). In tale contesto l'indicazione del permesso deve essere necessariamente
 * nel formato classe.codice, in questo modo si possono attibuire permessi di tipo core alla visualizzazione dei metodi, oppure permessi di altre classi.
 * Non ho usato la stessa notazione per i controlli in quanto li bisogna specificare anche l'istanza e diventerebbe complicato, ed inoltre non si 
 * potrebbero definire gli shortcut che nel 90% dei casi sono sufficienti.
 * Per recuperare i permessi di tutti i moduli istnziabili e non ed i permessi core da utilizzare in un form per una associazione, utilizzare
 * il metodo getForMulticheck di Permission
 *
 */

/**
 * \ingroup auth
 * Classe tipo model che rappresenta un permesso.
 *
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Permission extends Model {

  public static $table = TBL_PERMISSION;
  public static $table_perm_user = TBL_USER_PERMISSION;

  function __construct($id) {

    $this->_fields_label = array(
			'class' => _('Nome della classe'), 
			'code' => _('Codice del permesso'), 
			'label' => _('Label'), 
			'description' => _('Descrizione'), 
			'admin' => _('Richiede accesso area amministrativa'), 
		);

    $this->_tbl_data = TBL_PERMISSION;
    parent::__construct($id);
  }

  function __toString() {
		return $this->label;
	}
	
	public function getModelLabel() {
		return _('permesso');
	}

	/*
	 * Sovrascrive la struttura di default
	 * 
	 * @see Model::structure()
	 * @param integer $id
	 * @return array
	 */
	 public function structure($id) {

		$structure = parent::structure($id);

		$structure['admin'] = new BooleanField(array(
			'name'=>'admin', 
      'model'=>$this,
			'required'=>true,
			'enum'=>array(1 => _('si'), 0 => _('no')), 
			'default'=>0,
		));
		
		return $structure;
	}

  public static function getFromFullCode($code) {

    preg_match('#^(.*?)\.(.*)$#', $code, $matches);

    if(!$matches or count($matches)!= 3) {
      return null;
    }

    $class = $matches[1];
    $perm_code = $matches[2];

    $db = db::instance();
    $rows = $db->select('id', self::$table, "class='$class' AND code='$perm_code'");
    if($rows and count($rows)) {
      return new Permission($rows[0]['id']);
    }

    return null;
  }

  private static function getGroupedPermissions() {

    $db = db::instance();

    $res = array();

    $rows = $db->select('*', self::$table, null, array('order' => 'class, id'));
    foreach($rows as $row) {
      $class = $row['class'];
      $module_app = ModuleApp::getFromName($class);
      if($class === 'core' or !$module_app->instantiable) {
        if($class === 'core' or $module_app->active) {
          if(!isset($res[$class.',0'])) {
            $res[$class.',0'] = array();
          }
          $res[$class.',0'][] = new Permission($row['id']);
        }
      }
      else {
        $modules = ModuleInstance::getFromModuleApp($module_app->id);
        foreach($modules as $module) {
          if($module->active) {
            if(!isset($res[$module->name.','.$module->id])) {
              $res[$module->name.','.$module->id] = array();
            }
            $res[$module->name.','.$module->id][] = new Permission($row['id']);
          }
        }
      }
    }

    return $res;
  }

  public static function getForMulticheck() {

    Loader::import('sysClass', 'ModuleApp');
    Loader::import('module', 'ModuleInstance');

    $res = array();
    $grouped_permissions = self::getGroupedPermissions();
    foreach($grouped_permissions as $k => $perms) {
      preg_match("#(.*?),(\d*)#", $k, $matches);
      // instantiable module
      if(isset($matches[2]) and $matches[2] != 0) {
        $module = ModuleInstance::getFromName($matches[1]);
        foreach($perms as $p) {
          $res[$p->id.','.$matches[2]] = $module->label.' - '.$p->label;
        }
      }
      else {
        if($matches[1] === 'core') {
          $label = 'Sistema';
        }
        else {
          $module = ModuleApp::getFromName($matches[1]);
          $label = $module->label;
        }
        foreach($perms as $p) {
          $res[$p->id.',0'] = $label.' - '.$p->label;
        }
      }
    }

    return $res;
  }

	/**
	 * Elenco dei permessi
	 * 
	 * @return array array(perm_id, name, label)
	 * 
	 * Vengono mostrati: \n
	 *   - per i moduli non istanziabili -> i loro permessi
	 *   - per i moduli istanziabili -> i permessi dei moduli per ogni loro istanza
	 */
	public static function getList() {
		
		$db = db::instance();
		
		$items = array();
		
		$perm = $db->select('*', self::$table, '', array('order'=>'class ASC'));
		if($perm && count($perm))
		{
			foreach($perm AS $p)
			{
				$p_id = $p['id'];
				$p_class = $p['class'];
				$p_label = $p['label'];
				$p_description = $p['description'];
				
				$module_app = ModuleApp::getFromName($p_class);
				
				if($p_class === 'core' or !$module_app->instantiable)
				{	
					if($p_class === 'core' or $module_app->active)
					{
						/*if(!isset($res[$p_class.',0'])) {
							$res[$p_class.',0'] = array();
						}*/
						
						//$res[$p_class.',0'][] = new Permission($row['id']);
						
						if($p_class === 'core')
						{
							$mod_name = 'core';
							$mod_label = '';
						}
						else
						{
							$mod_name = $module_app->name; 
							$mod_label = $module_app->label; 
						}
						
						$items[] = array(
							'perm_id'=>$p_id, 
							'perm_label'=>$p_label, 
							'perm_descr'=>$p_description, 
							'mod_name'=>$mod_name, 
							'mod_label'=>$mod_label, 
							'inst_id'=>null
						);
					}
				}
				else
				{
					$modules = ModuleInstance::getFromModuleApp($module_app->id);
					
					foreach($modules as $module)
					{
						if($module->active)
						{
							/*if(!isset($res[$module->name.','.$module->id])) {
								$res[$module->name.','.$module->id] = array();
							}
							$res[$module->name.','.$module->id][] = new Permission($row['id']);*/
							
							$items[] = array(
								'perm_id'=>$p_id, 
								'perm_label'=>$p_label, 
								'perm_descr'=>$p_description, 
								'mod_name'=>$module->name, 
								'mod_label'=>$module->label, 
								'inst_id'=>$module->id
							);
						}
					}
				}
			}
		}
		
		return $items;
	}


}