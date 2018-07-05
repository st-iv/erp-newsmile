<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Mmit\NewSmile\StatusPatientTable,
    Mmit\NewSmile\PatientCardTable,
    Mmit\NewSmile\DoctorTable;

class CalendarComponent extends \CBitrixComponent
{

	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        $this->arResult['PATIENT_CARD'] = PatientCardTable::getArrayById(intval($this->arParams['ID']));
        if($this->arResult['PATIENT_CARD']['FIRST_VISIT']) {
            $this->arResult['PATIENT_CARD']['FIRST_VISIT'] = $this->arResult['PATIENT_CARD']['FIRST_VISIT']->format('Y-m-d\TH:i');
        }
        if($this->arResult['PATIENT_CARD']['PASSPORT_ISSUED_DATE']) {
            $this->arResult['PATIENT_CARD']['PASSPORT_ISSUED_DATE'] = $this->arResult['PATIENT_CARD']['PASSPORT_ISSUED_DATE']->format('Y-m-d');
        }
        if($this->arResult['PATIENT_CARD']['PASSPORT_ADDRESS_DATE']) {
            $this->arResult['PATIENT_CARD']['PASSPORT_ADDRESS_DATE'] = $this->arResult['PATIENT_CARD']['PASSPORT_ADDRESS_DATE']->format('Y-m-d');
        }
        if($this->arResult['PATIENT_CARD']['USER_PERSONAL_BIRTHDAY']) {
            $this->arResult['PATIENT_CARD']['USER_PERSONAL_BIRTHDAY'] = $this->arResult['PATIENT_CARD']['USER_PERSONAL_BIRTHDAY']->format('Y-m-d');
        }

        $this->getDoctors();
        $this->getStatusPatient();
	}

	protected function getStatusPatient()
    {
        $rsStatusPatient = StatusPatientTable::getList(array(
            'select' => array('ID', 'NAME')
        ));
        while ($arStatusPatient = $rsStatusPatient->fetch())
        {
            $this->arResult['STATUS_PATIENT'][] = $arStatusPatient;
        }
    }

    protected function getDoctors()
    {
        $rsDoctor = DoctorTable::getList(array(
            'select' => array('ID','NAME')
        ));
        while ($arDoctor = $rsDoctor->fetch())
        {
            $this->arResult['DOCTORS'][] = $arDoctor;
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