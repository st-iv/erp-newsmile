<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Mmit\NewSmile\VisitTable;

class CallPatientComponent extends \CBitrixComponent
{
	
	/**
	 * получение результатов
	 */
	protected function getResult()
	{
	    if (!Loader::includeModule('mmit.newSmile')) die();

        $rsVisit = VisitTable::getList(array(
            'filter' => array(
                "DATE_START" => new Date(date('d.m.Y')),
                'CLINIC_ID' => $_SESSION['CLINIC_ID']
            ),
            'select' =>array(
                '*',
                'UF_PATIENT_' => 'PATIENT',
                'UF_DOCTOR_' => 'DOCTOR'
            ),
            'order' => array(
                'TIME_START' => 'asc'
            )
        ));
        while ($arVisit = $rsVisit->Fetch())
        {
            $this->arResult['VISIT'][] = $arVisit;
        }
//        echo '<pre>';
//        print_r($this->arResult);
//        echo '</pre>';
	}
	
	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
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