<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use Bitrix\Bizproc;
use Bitrix\Crm;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class CBPSaleOrderCalceled
	extends CBPActivity
{
	private bool $writeLog;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->writeLog = true;
		$this->arProperties = [
			"Title" => "",
			"CancelStatusId" => "",
			"CancelReason" => ""
		];
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
			$result = $this->doCancelOrder($orderId);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	private function doCancelOrder(int $orderId = 0) : Main\Result
	{
		$result = new Main\Result();

		$order = Crm\Order\Order::load($orderId);

		if (!$order)
		{
			$this->writeLog(Loc::getMessage('CRM_SOAD_ORDER_NOT_FOUND'));

			$result->addError(new Main\Error(
				Loc::getMessage('CRM_SOAD_ORDER_NOT_FOUND')
			));

			return $result;
		}

		if ($order->isCanceled())
		{
			$this->writeLog(Loc::getMessage('CRM_SOAD_ORDER_IS_CANCELED'));

			$result->addError(new Main\Error(
				Loc::getMessage('CRM_SOAD_ORDER_IS_CANCELED')
			));
		}

		$cancelReason = (string)$this->CancelReason;
		$cancelStatusId = (string)$this->CancelStatusId;

		$result = $order->setField('STATUS_ID', $cancelStatusId);
		if ($result->isSuccess() && $cancelReason)
		{
			$result = $order->setField('REASON_CANCELED', $cancelReason);
		}

		if ($result->isSuccess())
		{
			$result = $order->save();
		}

		if (!$result->isSuccess())
		{
			foreach ($result->getErrorMessages() as $errorMessage)
			{
				$this->writeLog($errorMessage);
				$result->addError(new Main\Error(
					$errorMessage
				));
			}
		}

		return $result;
	}

	private function writeLog($mess = '', $type = \CBPTrackingType::Error, $modifyBy = 0 )
	{
		if ($this->writeLog && $mess !== '')
		{
			$this->WriteToTrackingService($mess, $modifyBy, $type);
		}
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null, $siteId = '')
	{
		$dialog = new Bizproc\Activity\PropertiesDialog(__FILE__, [
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
			'formName' => $formName,
			'siteId' => $siteId
		]);

		$statuses = [];

		foreach (Crm\Order\OrderStatus::getAllStatusesNames() as $statusId => $name)
		{
			if (Crm\Order\OrderStatus::getSemanticID($statusId) === Crm\PhaseSemantics::FAILURE)
			{
				$statuses[$statusId] = $name;
			}
		}

		$dialog->setMap([
			'CancelStatusId' => [
				'Name' => Loc::getMessage('CRM_SOAD_STATUS_NAME'),
				'FieldName' => 'cancel_status_id',
				'Type' => 'select',
				'Options' => $statuses
			],
			'CancelReason' => [
				'Name' => Loc::getMessage('CRM_SOAD_COMMENT_NAME'),
				'Description' => Loc::getMessage('CRM_SOAD_COMMENT_NAME'),
				'FieldName' => 'cancel_reason',
				'Type' => 'text'
			]
		]);

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$properties = [
			'CancelStatusId' => $arCurrentValues['cancel_status_id'],
			'CancelReason' => $arCurrentValues['cancel_reason'],
		];

		$errors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($errors) > 0)
		{
			return false;
		}

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$currentActivity["Properties"] = $properties;

		return true;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null) : array
	{
		$errors = [];
		if (empty($arTestProperties["CancelStatusId"]))
		{
			$errors[] = array("code" => "NotExist", "parameter" => "TargetStatus", "message" => GetMessage("CRM_SOAD_STATUS_ERROR"));
		}

		return array_merge($errors, parent::ValidateProperties($arTestProperties, $user));
	}
}