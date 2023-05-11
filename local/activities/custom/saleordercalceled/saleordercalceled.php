<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use Bitrix\Crm;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class CBPSaleOrderCalceled
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"MapFields" => null,
		);
	}

	public function Execute()
	{
		if (!Main\Loader::includeModule('crm') || !Main\Loader::includeModule('sale'))
		{
			return CBPActivityExecutionStatus::Closed;
		}

		[$entityTypeName, $entityId] = explode('_', $this->GetDocumentId()[2]);

		if ($entityTypeName !== \CCrmOwnerType::DealName)
		{
			$this->WriteToTrackingService(Loc::getMessage('CRM_SOAD_ORDER_ERROR'), 0, \CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$ordersIds = Crm\Binding\OrderEntityTable::getOrderIdsByOwner($entityId, \CCrmOwnerType::Deal);

		foreach ($ordersIds as $orderId)
		{
			$result = $this->updateOrderStatus($orderId);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	private function updateOrderStatus(int $orderId = 0) : Main\Result
	{
		$result = new Main\Result();

		return $result;
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		if ( !is_array($arCurrentValues) )
		{
			$arCurrentValues = [];

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);

			if (
				is_array($arCurrentActivity["Properties"])
				&& array_key_exists("MapFields", $arCurrentActivity["Properties"])
				&& is_array($arCurrentActivity["Properties"]["MapFields"])
			)
			{
				foreach ($arCurrentActivity["Properties"]["MapFields"] as $k => $v)
				{
					$arCurrentValues["MapFields"][$k] = $v;
				}
			}
		}

		return CBPRuntime::GetRuntime()->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			[
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
			]
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arProperties = ["MapFields" => []];

		if (
			is_array($arCurrentValues)
			&& count($arCurrentValues) > 0
			&& is_array($arCurrentValues["fields"])
			&& is_array($arCurrentValues["values"])
			&& count($arCurrentValues["fields"]) > 0
			&& count($arCurrentValues["values"]) > 0
		)
		{
			foreach($arCurrentValues["fields"] as $key => $value)
			{
				if ($value !== '' && $arCurrentValues["values"][$key] !== '')
				{
					$arProperties["MapFields"][$value] = $arCurrentValues["values"][$key];
				}
			}
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = [];

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}
}