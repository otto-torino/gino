<?php
/**
* @file form.php
* @brief Template del form di inserimento/modifica record in area amministrativa
*
* Variabili disponibili:
* - **open**: string, tag form
* - **hidden_inputs**: array
* - **inputs**: array
* - **submit**: string
* 
* @copyright 2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino; ?>
<? //@cond no-doxygen ?>

<!-- open form -->
<?php if($open): ?>
    <?= $open ?>
<?php endif ?>

<?php if(count($hidden_inputs)): ?>
    <? foreach($hidden_inputs AS $input): ?>
    	
    	<?= $input ?>
    	
    <? endforeach ?>
<?php endif ?>

<!-- input -->
<?php if(count($inputs)): ?>
    <? foreach($inputs AS $input): ?>
    	<!-- fieldset -->
    	<?php if(is_array($input) and array_key_exists('fieldset', $input) and $input['fieldset']): ?>
    		<fieldset>
    		<?php if($input['legend']): ?>
    			<legend><?= $input['legend'] ?></legend>
    		<?php endif ?>
    		
    		<?php if(array_key_exists('fields', $input) and count($input['fields'])): ?>
    			<? foreach($input['fields'] AS $i): ?>
    				
    				<?= $i ?>
    				
    			<? endforeach ?>
    		<?php endif ?>
    		</fieldset>
    	<!-- /fieldset -->
    	
    	<?php else: ?>
    		
    		<?= $input ?>
    		
    	<?php endif ?>
    	
    <? endforeach ?>
<?php endif ?>

<!-- submit -->
<?php if($submit): ?>
    <?= $submit ?>
<?php endif ?>

<!-- close form -->
<?php if($open): ?>
    </form>
<?php endif ?>


<? // @endcond ?>
