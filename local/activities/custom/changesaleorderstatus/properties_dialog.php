<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

$value = $arCurrentValues["MapFields"];
?>
<tr id="order_status" style="display:inline">
	<td colspan="2">
		<table width="100%" border="0" cellpadding="2" cellspacing="2" id="bwfvc_table1">
			<tbody>
			<tr style="display: none">
				<td>Lead ID</td>
				<td>
					<input id="id_var_name_0" type="hidden" name="fields[row0]" value="deal_id">
				</td>
				<td>
					<input id="id_var_value_0" type="hidden" name="values[row0]" value="{{ID}}">
				</td>
			</tr>
			<tr>
				<td>
					<label for="order_status_node">Order status: </label>
				</td>
				<td>
					<input id="id_var_name_1" type="hidden" size="5" name="fields[row1]" value="order_status">
				</td>
				<td>
					<input id="id_var_value_1" type="text" size="5" name="values[row1]" value="<?=$arCurrentValues["MapFields"]["order_status"]?>">
				</td>
			</tr>
			</tbody>
		</table>
	</td>
</tr>
