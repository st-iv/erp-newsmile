<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Mmit\NewSmile\WaitingListTable,
    Mmit\NewSmile\DoctorTable,
    Mmit\NewSmile\PatientCardTable,
    Mmit\NewSmile\ScheduleTable,
    Mmit\NewSmile\WorkChairTable;

class WaitingListCreateComponent extends \CBitrixComponent
{

    /**
     * обработка результатов
     */
    protected function requestResult()
    {
        $arFiled = array();
        if (!empty($this->request['PATIENT_ID'])) {
            $arFiled['PATIENT_ID'] = intval($this->request['PATIENT_ID']);
        }
        if (!empty($this->request['DOCTOR_ID'])) {
            $arFiled['DOCTOR_ID'] = intval($this->request['DOCTOR_ID']);
        }
        if (!empty($this->request['DATE'])) {
            $arDate = explode(',', $this->request['DATE']);
            $arFiled['DATE'] = json_encode($arDate);
        }if (!empty($this->request['DESCRIPTION'])) {
            $arFiled['DESCRIPTION'] = $this->request['DESCRIPTION'];
        }

        if (!empty($arFiled)) {
            try {
                WaitingListTable::add($arFiled);
            } catch (Exception $e) {
                ShowError($e->getMessage());
            }
        }
    }

	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        $this->arResult['CALENDAR'] = $this->setCalendar(time());

        $rsPatientCard = PatientCardTable::getList();
        while ($arPatientCard = $rsPatientCard->fetch())
        {
            $this->arResult['PATIENT'][] = $arPatientCard;
        }

        $rsDoctor = DoctorTable::getList();
        while ($arDoctor = $rsDoctor->fetch())
        {
            $this->arResult['DOCTOR'][] = $arDoctor;
        }
	}

    protected function setCalendar($intCurrentDate)
    {
        $arResult = array();
        $currentMonday = strtotime('Monday this week', $intCurrentDate);
        $this->startDay = date('Y.m.d', $currentMonday);
        for ($w = 0; $w < 5; $w++) {
            for ($d = 0; $d < 7; $d++) {
                $difference = mktime(0,0,0,0,$d + $w * 7,0) - mktime(0,0,0,0,0,0);
                $arResult[$w][$d] = date('Y-m-d', $currentMonday + $difference );
            }
        }
        $this->endDay = date('Y.m.d', $currentMonday + $difference);

        return $arResult;
    }
	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
        if (!Loader::includeModule('mmit.newSmile')) die();
		try
		{
		    $this->requestResult();
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