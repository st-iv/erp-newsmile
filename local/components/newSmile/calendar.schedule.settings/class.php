<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Config\Option,
    Mmit\NewSmile\VisitTable,
    Mmit\NewSmile\ScheduleTemplateTable,
    Mmit\NewSmile\DoctorTable,
    Mmit\NewSmile\WorkChairTable;
use Mmit\NewSmile\ScheduleTable;

class CalendarScheduleSettingsComponent extends \CBitrixComponent
{

    /*
     * обработка запроса
     * */
    protected function requestResult($request)
    {
        if (!empty($request['ACTION'])) {
            if ($request['ACTION'] == 'ADD_SCHEDULE') {
                ScheduleTable::addWeekSchedule(date('Y-m-d'), $_SESSION['CLINIC_ID']);
                ScheduleTable::addWeekSchedule(date('Y-m-d', strtotime('+1 weeks')), $_SESSION['CLINIC_ID']);
                ScheduleTable::addWeekSchedule(date('Y-m-d', strtotime('+2 weeks')), $_SESSION['CLINIC_ID']);
                ScheduleTable::addWeekSchedule(date('Y-m-d', strtotime('+3 weeks')), $_SESSION['CLINIC_ID']);
            }
        }
    }
	
	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        $this->arResult['DOCTOR_ID'] = array();
        $this->arResult['DOCTOR_ID'] = array();
	    $this->arResult['THIS_DATE'] = '1993-04-26';
	    if (!empty($this->request['THIS_DATE'])) {
            $this->arResult['THIS_DATE'] = $this->request['THIS_DATE'];
        }
        $this->getWorkChair();
        $this->getVisit($this->arResult['THIS_DATE']);
        $this->getSchedule($this->arResult['THIS_DATE']);
        $this->getMainDoctors($this->arResult['THIS_DATE']);
        $this->getDoctor();
        $this->getAllDoctor();

	}

	protected function getVisit($date)
    {
        $arTimeStart = explode(':', Option::get('mmit.newsmile', "start_time_schedule", '00:00'));
        $arTimeEnd = explode(':', Option::get('mmit.newsmile', "end_time_schedule", '00:00'));

        $timeStart = mktime($arTimeStart[0],$arTimeStart[1],0,0,0,0);
        $timeEnd = mktime($arTimeEnd[0],$arTimeEnd[1],0,0,0,0);

        $arKeyTime = array();
        $arResult = array();

        while ($timeStart < $timeEnd) {
            $arKeyTime[] = date('H:i', $timeStart);
            $arResult[] = array(
                'NAME' => date('H:i', $timeStart),
                'WORK_CHAIR' => $this->arResult['WORK_CHAIR']
            );
            $timeStart += 900;
        }

        $rsVisit = VisitTable::getList(array(
            'filter' => array(
                'DATE_START' => new Date($date, 'Y-m-d'),
                'CLINIC_ID' => $_SESSION['CLINIC_ID']
            ),
            'select' => array(
                '*',
                'UF_PATIENT_' => 'PATIENT'
            )
        ));
        while ($arVisit = $rsVisit->Fetch())
        {
            if (($key = array_search($arVisit['TIME_START']->format('H:i'), $arKeyTime)) !== false) {
                $arResult[$key]['WORK_CHAIR'][$arVisit['WORK_CHAIR_ID']] = $arVisit;
            }
            //$this->arResult['VISIT'][] = $arVisit;
        }
        $this->arResult['VISIT'] = $arResult;
//        echo '<pre>';
//        print_r($arResult);
//        echo '</pre>';
    }

    protected function getMainDoctors($date)
    {
        // TODO отрефакторить когда будет верстка и можно будет поменять формат вывода данных
        $dbMainDoctors = \Mmit\NewSmile\MainDoctorTemplateTable::getList([
            'filter' => [
                'WORK_CHAIR_ID' => array_keys($this->arResult['WORK_CHAIR']),
                'DATE' => new Date($date, 'Y-m-d')
            ],
            'select' => ['WORK_CHAIR_ID', 'DOCTOR_ID', 'SECOND_DAY_HALF']
        ]);

        $mainDoctors = array();

        while($mainDoctor = $dbMainDoctors->fetch())
        {
            $mainDoctors[$mainDoctor['WORK_CHAIR_ID']][(int)$mainDoctor['SECOND_DAY_HALF']] = $mainDoctor['DOCTOR_ID'];
            $this->arResult['MAIN_DOCTOR_ID'][$mainDoctor['DOCTOR_ID']] = $mainDoctor['DOCTOR_ID'];
            $this->arResult['WORK_CHAIR'][$mainDoctor['WORK_CHAIR_ID']]['MAIN_DOCTOR'][$mainDoctor['DOCTOR_ID']] = $mainDoctor['DOCTOR_ID'];
        }

        foreach ($this->arResult['SCHEDULE'] as $time => &$chairsSchedule)
        {
            $isSecondDayHalf = strtotime(date('d-m-Y ' . $time)) >= strtotime(date('d-m-Y 15:00'));

            foreach ($chairsSchedule as $workChairId => &$schedule)
            {
                $schedule['MAIN_DOCTOR_ID'] = $mainDoctors[$workChairId][(int)$isSecondDayHalf];
            }

            unset($schedule);
        }

        unset($chairsSchedule);
    }

    protected function getSchedule($date)
    {
        $rsSchedule = ScheduleTemplateTable::getList(array(
            'filter' => array(
                'CLINIC_ID' => \Mmit\NewSmile\Config::getClinicId(),
                '>=TIME' => new Date($date, 'Y-m-d'),
                '<=TIME' => new Date(date('Y-m-d', strtotime($date) + 86400), 'Y-m-d')
            )
        ));
        while ($arSchedule = $rsSchedule->fetch())
        {
            $this->arResult['SCHEDULE'][$arSchedule['TIME']->format('H:i')][$arSchedule['WORK_CHAIR_ID']] = array(
                'ID' => $arSchedule['ID'],
                'DOCTOR_ID' => $arSchedule['DOCTOR_ID'],
                'MAIN_DOCTOR_ID' => $arSchedule['MAIN_DOCTOR_ID'],
                'PATIENT_ID' => $arSchedule['PATIENT_ID']
            );
            if ($arSchedule['DOCTOR_ID']) {
                $this->arResult['DOCTOR_ID'][$arSchedule['DOCTOR_ID']] = $arSchedule['DOCTOR_ID'];
                $this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['DOCTOR'][$arSchedule['DOCTOR_ID']] = $arSchedule['DOCTOR_ID'];
            }
            if ($arSchedule['MAIN_DOCTOR_ID']) {
                $this->arResult['MAIN_DOCTOR_ID'][$arSchedule['MAIN_DOCTOR_ID']] = $arSchedule['MAIN_DOCTOR_ID'];
                $this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['MAIN_DOCTOR'][$arSchedule['MAIN_DOCTOR_ID']] = $arSchedule['MAIN_DOCTOR_ID'];
            }
        }
    }

    protected function getWorkChair()
    {
        $rsWorkChair = WorkChairTable::getList([
            'filter' => [
                'CLINIC_ID' => $_SESSION['CLINIC_ID']
            ]
        ]);
        while ($arWorkChair = $rsWorkChair->Fetch())
        {
            $this->arResult['WORK_CHAIR'][$arWorkChair['ID']] = $arWorkChair;
        }
    }

    protected function getDoctor()
    {
        $rsDoctor = DoctorTable::getList(array(
            'filter' => array(
                'ID' => array_merge($this->arResult['DOCTOR_ID'], $this->arResult['MAIN_DOCTOR_ID']),
                'CLINIC_ID' => $_SESSION['CLINIC_ID']
            )
        ));
        while ($arDoctor = $rsDoctor->fetch())
        {
            if (array_search($arDoctor['ID'], $this->arResult['DOCTOR_ID'])) {
                $this->arResult['DOCTOR_ID'][$arDoctor['ID']] = $arDoctor;
            }
            if (array_search($arDoctor['ID'], $this->arResult['MAIN_DOCTOR_ID'])) {
                $this->arResult['MAIN_DOCTOR_ID'][$arDoctor['ID']] = $arDoctor;
            }
        }
    }
    protected function getAllDoctor()
    {
        $rsDoctor = DoctorTable::getList([
            'filter' => [
                $_SESSION['CLINIC_ID']
            ],
            'select' => [
                'ID', 'NAME'
            ]
        ]);
        while ($arDoctor = $rsDoctor->fetch())
        {
            $this->arResult['DOCTORS'][$arDoctor['ID']] = $arDoctor['NAME'];
        }
    }
	
	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
		try
		{
            if (!Loader::includeModule('mmit.newSmile')) die();
			$this->getResult();
			$this->requestResult($this->request);
//            echo '<pre>';
//            print_r($this->arResult);
//            echo '</pre>';
			$this->includeComponentTemplate();
		}
		catch (Exception $e)
		{
			ShowError($e->getMessage());
		}
	}
}
?>