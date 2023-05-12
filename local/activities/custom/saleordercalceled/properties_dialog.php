<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

foreach ($dialog->getMap() as $fieldId => $field)
{
	?>
	<tr>
		<td align="right" width="40%">
			<?= ($field['Required'] ? '<span class="adm-required-field">' : '') . htmlspecialcharsbx($field['Name']) . ':' . ($field['Required'] ? '</span>' : '') ?>
		</td>
		<td width="60%">
			<?php
			$filedType = $dialog->getFieldTypeObject($field);

			echo $filedType->renderControl([
				'Form' => $dialog->getFormName(),
				'Field' => $field['FieldName']
			], $dialog->getCurrentValue($field['FieldName']), true, 0);
			?>
		</td>
	</tr>
	<?php
}
