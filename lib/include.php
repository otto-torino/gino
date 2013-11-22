<?php
/**
 * @file include.php
 * @brief Include le librerie che non sono ancora state caricate in gino
 *
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * @copyright 2005 Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Alcune costanti generali
 */
include_once(CLASSES_DIR.OS."class.locale.php");
include_once(CLASSES_DIR.OS."class.db.php");
include_once(CLASSES_DIR.OS."class.registry.php");
include_once(CLASSES_DIR.OS."class.translation.php");
include_once(CLASSES_DIR.OS."class.pub.php");
include_once(CLASSES_DIR.OS."class.cache.php");
include_once(CLASSES_DIR.OS."class.error.php");
include_once(CLASSES_DIR.OS."class.model.php");
include_once(CLASSES_DIR.OS."class.view.php");
include_once(CLASSES_DIR.OS."class.controller.php");
include_once(CLASSES_DIR.OS."class.link.php");
include_once(CLASSES_DIR.OS."class.html.php");
include_once(CLASSES_DIR.OS."class.htmlSection.php");
include_once(CLASSES_DIR.OS."class.htmlArticle.php");
include_once(CLASSES_DIR.OS."class.htmlList.php");
include_once(CLASSES_DIR.OS."class.htmlTab.php");
include_once(CLASSES_DIR.OS."class.template.php");
include_once(CLASSES_DIR.OS."class.frontend.php");
include_once(CLASSES_DIR.OS."class.css.php");
include_once(CLASSES_DIR.OS."class.skin.php");
include_once(CLASSES_DIR.OS."class.javascript.php");
include_once(CLASSES_DIR.OS."class.document.php");

include_once(LIB_DIR.OS."autoInclude.php");
include_once(LIB_DIR.OS."func.php");

include_once(CLASSES_DIR.OS."class.auth.php");
include_once(CLASSES_DIR.OS."class.pagelist.php");
include_once(CLASSES_DIR.OS."class.form.php");
include_once(CLASSES_DIR.OS."class.admin.php");
include_once(CLASSES_DIR.OS."class.options.php");
include_once(CLASSES_DIR.OS."class.account.php");

include_once(CLASSES_DIR.OS.'class.adminTable.php');
include_once(CLASSES_DIR.OS.'class.inputForm.php');

include_once(FIELDS_DIR.OS.'class.field.php');
include_once(FIELDS_DIR.OS.'class.booleanField.php');
include_once(FIELDS_DIR.OS.'class.charField.php');
include_once(FIELDS_DIR.OS.'class.constantField.php');
include_once(FIELDS_DIR.OS.'class.dateField.php');
include_once(FIELDS_DIR.OS.'class.datetimeField.php');
include_once(FIELDS_DIR.OS.'class.directoryField.php');
include_once(FIELDS_DIR.OS.'class.emailField.php');
include_once(FIELDS_DIR.OS.'class.enumField.php');
include_once(FIELDS_DIR.OS.'class.fileField.php');
include_once(FIELDS_DIR.OS.'class.floatField.php');
include_once(FIELDS_DIR.OS.'class.foreignKeyField.php');
include_once(FIELDS_DIR.OS.'class.manyToManyField.php');
include_once(FIELDS_DIR.OS.'class.hiddenField.php');
include_once(FIELDS_DIR.OS.'class.imageField.php');
include_once(FIELDS_DIR.OS.'class.integerField.php');
include_once(FIELDS_DIR.OS.'class.textField.php');
include_once(FIELDS_DIR.OS.'class.timeField.php');
include_once(FIELDS_DIR.OS.'class.yearField.php');

?>
