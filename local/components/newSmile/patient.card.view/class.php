<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Mmit\NewSmile\PatientCardTable,
    Mmit\NewSmile\DoctorTable,
    Mmit\NewSmile\Status,
    Mmit\NewSmile\Visit\VisitTable;

class CalendarComponent extends \CBitrixComponent
{

    /**
     * получение результатов
     */
    protected function getResult()
    {
        if ($this->arParams['ID'] !== 0) {
            $rsResult = PatientCardTable::getList([
                'filter' => [
                    'ID' => $this->arParams['ID']
                ]
            ]);
            if ($arResult = $rsResult->fetch()) {
                $this->arResult['PATIENT_CARD'] = $arResult;
            } else {
                die('Пациент не найден');
            }

            if ($this->arResult['PATIENT_CARD']['FIRST_VISIT']) {
                $this->arResult['PATIENT_CARD']['FIRST_VISIT'] = $this->arResult['PATIENT_CARD']['FIRST_VISIT']->format('Y-m-d\TH:i');
            }
            if ($this->arResult['PATIENT_CARD']['PASSPORT_ISSUED_DATE']) {
                $this->arResult['PATIENT_CARD']['PASSPORT_ISSUED_DATE'] = $this->arResult['PATIENT_CARD']['PASSPORT_ISSUED_DATE']->format('Y-m-d');
            }
            if ($this->arResult['PATIENT_CARD']['PASSPORT_ADDRESS_DATE']) {
                $this->arResult['PATIENT_CARD']['PASSPORT_ADDRESS_DATE'] = $this->arResult['PATIENT_CARD']['PASSPORT_ADDRESS_DATE']->format('Y-m-d');
            }
            if ($this->arResult['PATIENT_CARD']['USER_PERSONAL_BIRTHDAY']) {
                $this->arResult['PATIENT_CARD']['USER_PERSONAL_BIRTHDAY'] = $this->arResult['PATIENT_CARD']['USER_PERSONAL_BIRTHDAY']->format('Y-m-d');
            }
        } else {
            die('Пациент не найден');
        }

        $this->getDoctors();
        $this->getStatusPatient();
    }

    protected function getStatusPatient()
    {
        $rsStatusPatient = Status\PatientTable::getList(array(
            'select' => array('ID', 'NAME')
        ));
        while ($arStatusPatient = $rsStatusPatient->fetch())
        {
            $this->arResult['STATUS_PATIENT'][] = $arStatusPatient;
            if ($arStatusPatient['ID'] == $this->arResult['PATIENT_CARD']['STATUS_ID']) {
                $this->arResult['PATIENT_CARD']['STATUS_NAME'] = $arStatusPatient['NAME'];
            }
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
            if (in_array($arDoctor['ID'], $this->arResult['PATIENT_CARD']['DOCTORS_ID'])) {
                $this->arResult['PATIENT_CARD']['DOCTORS_NAME'][] = $arDoctor['NAME'];
            }
        }
    }

    protected function getFirstLastVisit()
    {
        $rsVisit = VisitTable::getList(array(
            'order' => array(
                'TIME_START' => 'ASC'
            ),
            'select' => array(
                'TIME_START'
            )
        ));
        if ($arVisit = $rsVisit->fetch()) {
            $this->arResult['PATIENT_CARD']['DOCTORS_NAME']['FIRST_VISIT'] -> $arVisit['TIME_START']->format('Y-m-d');
        }
        $rsVisit = VisitTable::getList(array(
            'order' => array(
                'TIME_START' => 'DESC'
            ),
            'select' => array(
                'TIME_START'
            )
        ));
        if ($arVisit = $rsVisit->fetch()) {
            $this->arResult['PATIENT_CARD']['DOCTORS_NAME']['LAST_VISIT'] -> $arVisit['TIME_START']->format('Y-m-d');
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