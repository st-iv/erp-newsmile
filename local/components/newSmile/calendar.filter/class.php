<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Type\DateTime,
    Bitrix\Main\Config\Option,
    Mmit\NewSmile\VisitTable,
    Mmit\NewSmile\ScheduleTable,
    Mmit\NewSmile\DoctorTable,
    Mmit\NewSmile\PatientCardTable,
    Mmit\NewSmile\WorkChairTable;

class CalendarFilterComponent extends \CBitrixComponent
{
    private $FILTER_NAME = '';
	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        $this->getWorkChair();
        $this->getDoctors();
        $this->getPatients();
        $this->getFilter();
	}

	protected function getFilter()
    {
        $arResult['FILTER_TIME'] = [
            '8:00',
            '9:00',
            '10:00',
            '11:00',
            '12:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
            '17:00',
            '18:00',
            '19:00',
            '20:00',
            '21:00',
            '22:00',
            '23:00',
        ];
        $this->arResult['FILTER'] = $arResult;
    }

    protected function getWorkChair()
    {
        $isResult = false;
        $rsWorkChair = WorkChairTable::getList([
            'filter' => [
                'CLINIC_ID' => $_SESSION['CLINIC_ID']
            ]
        ]);
        while ($arWorkChair = $rsWorkChair->Fetch())
        {
            $this->arResult['WORK_CHAIR'][] = $arWorkChair;
            $isResult = true;
        }
        return $isResult;
    }

    protected function getDoctors()
    {
        $rsDoctor = DoctorTable::getList(array(
            'select' => array(
                'ID', 'NAME'
            ),
            'filter' => [
                'CLINIC_ID' => $_SESSION['CLINIC_ID']
            ]
        ));
        while ($arDoctor = $rsDoctor->fetch())
        {
            $this->arResult['DOCTORS'][] = $arDoctor;
        }
    }

    protected function getPatients()
    {
        $rsPatient = PatientCardTable::getList(array(
            'select' => array(
                'ID', 'NAME'
            )
        ));
        while ($arPatient = $rsPatient->fetch())
        {
            $this->arResult['PATIENTS'][] = $arPatient['NAME'];
        }
    }

    protected function requestResult($request)
    {
        $arResult = [];
        if (!empty($request['TIME_FROM'])) {
            $arResult['TIME_FROM'] = $request['TIME_FROM'];
        }
        if (!empty($request['TIME_TO'])) {
            $arResult['TIME_TO'] = $request['TIME_TO'];
        }
        if (!empty($request['DOCTOR'])) {
            $arResult['DOCTOR'] = $request['DOCTOR'];
        }
        global ${$this->FILTER_NAME};
        ${$this->FILTER_NAME} = $arResult;
    }
	
	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
		try
		{
            if (!Loader::includeModule('mmit.newSmile')) die();
            if (empty($this->arParams['FILTER_NAME'])) {
                $this->FILTER_NAME = $this->arParams['FILTER_NAME'];
            } else {
                $this->FILTER_NAME = 'arFilter';
            }
			$this->getResult();
            $this->requestResult($this->request);
			$this->includeComponentTemplate();
		}
		catch (Exception $e)
		{
			ShowError($e->getMessage());
		}
	}
}
?>