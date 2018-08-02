<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile\Status,
    Mmit\NewSmile\PatientCardTable,
    Mmit\NewSmile\DoctorTable;
use Mmit\NewSmile\WorkChairTable;

class WorkChairEditComponent extends \CBitrixComponent
{

    protected function requestResult($request)
    {
        $arFields = array();
        if (!empty($request['NAME'])) {
            $arFields['NAME'] = $request['NAME'];
        }
        if (!empty($request['CLINIC_ID'])) {
            $arFields['CLINIC_ID'] = intval($request['CLINIC_ID']);
        }

        if (!empty($arFields)) {
            if (!empty($this->arParams['ID'])) {
                WorkChairTable::update($this->arParams['ID'], $arFields);
            } else {
                $res = WorkChairTable::add($arFields);
                if ($res->isSuccess()) {
                    LocalRedirect('/work-chair/' . $res->getId());
                }
            }
        }
    }

	/**
	 * получение результатов
	 */
	protected function getResult()
	{
	    if (!empty($this->arParams['ID'])) {
            $rsResult = WorkChairTable::getList([
                'filter' => [
                    'ID' => $this->arParams['ID']
                ]
            ]);
            if ($arResult = $rsResult->fetch()) {
                $this->arResult['WORK_CHAIR'] = $arResult;
            }
        }
	}

	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
        if (!Loader::includeModule('mmit.newSmile')) die();
		try
		{
		    $this->requestResult($this->request);
			$this->getResult();
			$this->includeComponentTemplate();
		}
		catch (Exception $e)
		{
			ShowError($e->getMessage());
		}
	}
}
?>