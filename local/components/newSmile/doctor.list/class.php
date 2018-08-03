<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Mmit\NewSmile\PatientCardTable,
    Mmit\NewSmile\DoctorTable,
    Mmit\NewSmile\Status,
    Mmit\NewSmile\VisitTable;

class DoctorListComponent extends \CBitrixComponent
{

    /**
     * получение результатов
     */
    protected function getResult()
    {
        global $USER;
        $arFilter = [];
        if (!$USER->IsAdmin()) {
            $arFilter['CLINIC_ID'] = $_SESSION['CLINIC_ID'];
        }

        $rsResult = DoctorTable::getList([
            'filter' => $arFilter
        ]);
        while ($arResult = $rsResult->fetch())
        {
            $this->arResult['DOCTORS'][] = $arResult;
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