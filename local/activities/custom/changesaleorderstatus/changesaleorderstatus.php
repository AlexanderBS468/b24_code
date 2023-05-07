<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

class CBPChangeSaleOrderStatus
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
		if (is_array($this->MapFields) && count($this->MapFields))
		{
			$values = $this->__get("MapFields");

		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		if ( !is_array($arCurrentValues) )
		{
			$arCurrentValues = [];

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);

			if (
				is_array($arCurrentActivity["Properties"])
				&& array_key_exists("MapFields", $arCurrentActivity["Properties"])
				&& is_array($arCurrentActivity["Properties"]["MapFields"]))
			{
				foreach ($arCurrentActivity["Properties"]["MapFields"] as $k => $v)
				{
					$arCurrentValues["MapFields"][$k] = $v;
				}
			}
		}

		$runtime = CBPRuntime::GetRuntime();
		return $runtime->ExecuteResourceFile(
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
		$runtime = CBPRuntime::GetRuntime();
		$arProperties = array("MapFields" => array());

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