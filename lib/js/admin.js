
/**
 * @brief Per selezionare/deselezionare tutti i checkbox di FieldName
 * @param FormName, string
 * @param FieldName, string
 * @param CheckValue, boolean
 * @returns
 * 
 * @example
 * @code
 * <input type="button" value="check all" onclick="SetAllCheckBoxes('form_name', 'fields[]', true)" />
 * <input type="button" value="uncheck all" onclick="SetAllCheckBoxes('form_name', 'fields[]', false)" />
 * @endcode
 */
function SetAllCheckBoxes(FormName, FieldName, CheckValue) {
	if(!document.forms[FormName])
		return;

	var objCheckBoxes = document.forms[FormName].elements[FieldName];
	if(!objCheckBoxes)
		return;

	var countCheckBoxes = objCheckBoxes.length;
	if(!countCheckBoxes)
		objCheckBoxes.checked = CheckValue;
	else
		// set the check value for all check boxes
		for(var i = 0; i < countCheckBoxes; i++)
			objCheckBoxes[i].checked = CheckValue;
}
