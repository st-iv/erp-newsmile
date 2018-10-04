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
    Mmit\NewSmile\WorkChairTable,
    Bitrix\Main\ORM\Query\Query;

class CalendarDayComponent extends \CBitrixComponent
{
    protected $thisDate = '';

    public function onPrepareComponentParams($arParams)
    {
        if($arParams['FILTER'] instanceof \Bitrix\Main\ORM\Query\Filter\ConditionTree)
        {
            $arParams['FILTER'] = clone $arParams['FILTER'];
        }
        else
        {
            $arParams['FILTER'] = Query::filter();
        }

        return $arParams;
    }

    /**
	 * получение результатов
	 */
	protected function getResult()
	{
        $this->thisDate = date('Y-m-d');
	    if (!empty($this->request['THIS_DATE'])) {
            $this->thisDate = $this->request['THIS_DATE'];
        }
        $this->arResult['THIS_DATE'] = $this->thisDate;

        $isNext = $this->getWorkChair();
        $isNext = $isNext && $this->getSchedule();
        $isNext = $isNext && $this->getVisit();

        $this->getDoctors();
        $this->getPatients();
	}

	protected function getScheduleFilter()
    {
        /**
         * @var \Bitrix\Main\ORM\Query\Filter\ConditionTree $filter
         */
        $filter = $this->arParams['FILTER'];

        $thisDate = new \DateTime($this->thisDate);
        $tomorrowDate = clone $thisDate;
        $tomorrowDate->modify('tomorrow');

        $filter->whereBetween('TIME', Date::createFromPhp($thisDate), Date::createFromPhp($tomorrowDate));

        return $filter;
    }

    protected function getSchedule()
    {
        $isResult = false;

        $rsSchedule = ScheduleTable::getList(array(
            'order' => array(
                'TIME' => 'ASC'
            ),
            'filter' => $this->getScheduleFilter(),
            'select' => array(
                'ID',
                'TIME',
                'UF_DOCTOR_' => 'DOCTOR',
                'UF_MAIN_DOCTOR_' => 'MAIN_DOCTOR',
                'WORK_CHAIR_ID',
                'CLINIC_ID',
                'PATIENT_ID'
            )
        ));

        while ($arSchedule = $rsSchedule->fetch())
        {
            $this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['SCHEDULES'][] = $arSchedule;
            if (empty($this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['DOCTORS'][$arSchedule['UF_DOCTOR_ID']])) {
                $this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['DOCTORS'][$arSchedule['UF_DOCTOR_ID']] = array(
                    'NAME' => $arSchedule['DOCTOR_NAME']
                );
            }

            if ($arSchedule['TIME']->getTimestamp() < strtotime($arSchedule['TIME']->format('Y-m-d 15:00'))) {
                $time = $arSchedule['TIME']->format('Y-m-d 9:00');
            } else {
                $time = $arSchedule['TIME']->format('Y-m-d 15:00');
            }
            if (!empty($arSchedule['UF_MAIN_DOCTOR_ID'])) {
                $tempField = array(
                    'ID' => $arSchedule['UF_MAIN_DOCTOR_ID'],
                    'NAME' => $arSchedule['UF_MAIN_DOCTOR_NAME'],
                    'LAST_NAME' => $arSchedule['UF_MAIN_DOCTOR_LAST_NAME'],
                    'SECOND_NAME' => $arSchedule['UF_MAIN_DOCTOR_SECOND_NAME'],
                    'TIME' => $time
                );
                if (array_search($tempField,$this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['MAIN_DOCTORS']) === null ||
                    array_search($tempField,$this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['MAIN_DOCTORS']) === false) {
                    $this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['MAIN_DOCTORS'][] = $tempField;
                }
            } else {
                $tempField = array(
                    'ID' => 0,
                    'TIME' => $time
                );
                if (array_search($tempField,$this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['MAIN_DOCTORS']) === null ||
                    array_search($tempField,$this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['MAIN_DOCTORS']) === false) {
                    $this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['MAIN_DOCTORS'][] = $tempField;
                }
            }

            $isResult = true;
        }
        return $isResult;
    }

	protected function getVisit()
    {
        $isResult = false;
        $arResult = array();
        $rsVisit = VisitTable::getList(array(
            'order' => array(
                'TIME_START' => 'ASC'
            ),
            'filter' => array(
                'DATE_START' => new Date($this->thisDate, 'Y-m-d'),
                'CLINIC_ID' => \Mmit\NewSmile\Config::getClinicId()
            ),
            'select' => array(
                'ID',
                'TIME_START',
                'TIME_END',
                'UF_PATIENT_' => 'PATIENT',
                'UF_DOCTOR_' => 'DOCTOR',
                'STATUS_ID',
                'STATUS_NAME' => 'STATUS.NAME',
                'WORK_CHAIR_ID',
            )
        ));
        while ($arVisit = $rsVisit->Fetch())
        {
            $arResult[] = $arVisit;
            $isResult = true;
        }

        $timeStart = strtotime($this->thisDate . ' ' . Option::get('mmit.newsmile', "start_time_schedule", '00:00'));
        $timeEnd = strtotime($this->thisDate . ' ' . Option::get('mmit.newsmile', "end_time_schedule", '00:00'));
        foreach ($arResult as $arVisit)
        {
            while ($timeStart < $arVisit['TIME_START']->getTimestamp())
            {
                $this->arResult['WORK_CHAIR'][$arVisit['WORK_CHAIR_ID']]['VISITS'][] = array(
                    'TIME_START' => new DateTime(date('Y-m-d H:i',$timeStart), 'Y-m-d H:i'),
                    'TIME_END' => new DateTime(date('Y-m-d H:i',$timeStart + ScheduleTable::TIME_15_MINUTES), 'Y-m-d H:i'),
                );
                $timeStart += ScheduleTable::TIME_15_MINUTES;
            }
            $this->arResult['WORK_CHAIR'][$arVisit['WORK_CHAIR_ID']]['VISITS'][] = $arVisit;
            $timeStart = $arVisit['TIME_END']->getTimestamp();
        }
        while ($timeStart < $timeEnd)
        {
            $this->arResult['WORK_CHAIR'][$arVisit['WORK_CHAIR_ID']]['VISITS'][] = array(
                'TIME_START' => new DateTime(date('Y-m-d H:i',$timeStart), 'Y-m-d H:i'),
                'TIME_END' => new DateTime(date('Y-m-d H:i',$timeStart + ScheduleTable::TIME_15_MINUTES), 'Y-m-d H:i'),
            );
            $timeStart += ScheduleTable::TIME_15_MINUTES;
        }
//        $this->arResult['WORK_CHAIR'][$arVisit['WORK_CHAIR_ID']]['VISITS'] = $arResult;
        return $isResult;
    }

    protected function getWorkChair()
    {
        $isResult = false;
        $rsWorkChair = WorkChairTable::getList([
            'filter' => [
                'CLINIC_ID' => \Mmit\NewSmile\Config::getClinicId()
            ]
        ]);
        while ($arWorkChair = $rsWorkChair->Fetch())
        {
            $this->arResult['WORK_CHAIR'][$arWorkChair['ID']] = $arWorkChair;
            $isResult = true;
        }
        return $isResult;
    }

    protected function getDoctors()
    {
        $rsDoctor = DoctorTable::getList(array(
            'select' => array(
                'ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'
            ),
            'filter' => [
                'CLINIC_ID' => \Mmit\NewSmile\Config::getClinicId()
            ]
        ));
        while ($arDoctor = $rsDoctor->fetch())
        {
            $this->arResult['DOCTORS'][$arDoctor['ID']] = $arDoctor;
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
            $this->arResult['PATIENTS'][$arPatient['ID']] = $arPatient['NAME'];
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
			$this->includeComponentTemplate();
		}
		catch (Exception $e)
		{
			ShowError($e->getMessage());
		}
	}
}
?>