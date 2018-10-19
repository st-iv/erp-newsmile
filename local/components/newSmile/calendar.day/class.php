<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Type\DateTime,
    Bitrix\Main\Config\Option,
    Mmit\NewSmile,
    Mmit\NewSmile\VisitTable,
    Mmit\NewSmile\ScheduleTable,
    Mmit\NewSmile\DoctorTable,
    Mmit\NewSmile\PatientCardTable,
    Mmit\NewSmile\WorkChairTable,
    Bitrix\Main\ORM\Query\Query;

class CalendarDayComponent extends \CBitrixComponent
{
    protected $thisDate = '';

    /**
     * @var \DateTime
     */
    protected $thisDateTime;

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

    protected function processRequest(\Bitrix\Main\HttpRequest $request)
    {
        if(check_bitrix_sessid() && isset($request['calendar_day']))
        {
            switch($request['action'])
            {
                case 'change_doctor':
                    $this->processChangeDoctor($request);
                    break;
            }
        }
    }

    protected function processChangeDoctor(\Bitrix\Main\HttpRequest $request)
    {
        $visitId = (int)$request['visit_id'];
        $scheduleId = (int)$request['schedule_id'];
        $doctorId = (int)$request['doctor_id'];

        if(!$visitId && !$scheduleId) return;

        if($visitId)
        {
            if(!$doctorId) return;

            $this->processChangeVisitDoctor($visitId, $doctorId);
        }
        elseif ($scheduleId)
        {
            ScheduleTable::update($scheduleId, [
                'DOCTOR_ID' => $doctorId
            ]);
        }
    }

    protected function processChangeVisitDoctor($visitId, $doctorId)
    {
        $dbVisit = VisitTable::getByPrimary(['ID' => $visitId], [
            'select' => ['TIME_START', 'TIME_END']
        ]);

        $visit = $dbVisit->fetch();

        if(!$visit) return;

        VisitTable::update($visitId, [
            'DOCTOR_ID' => $doctorId
        ]);

        $dbSchedules = ScheduleTable::getList([
            'filter' => [
                '>=TIME' => $visit['TIME_START'],
                '<TIME' => $visit['TIME_END'],
            ],
            'select' => ['ID']
        ]);

        while($schedule = $dbSchedules->fetch())
        {
            ScheduleTable::update($schedule['ID'], [
                'DOCTOR_ID' => $doctorId
            ]);
        }
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

        $this->thisDateTime = new \DateTime($this->thisDate);

        $this->arResult['THIS_DATE'] = $this->thisDate;

        $isNext = $this->writeWorkChair();
        $isNext = $isNext && $this->writeSchedule();
        $this->writeTimeLine();
        $isNext = $isNext && $this->writeVisit();

        $this->writeDoctors();
        $this->writePatients();



        $this->arResult['START_TIME'] = urldecode($this->request['TIME_FROM']) ?: NewSmile\Config::getScheduleStartTime();
        $this->arResult['END_TIME'] = urldecode($this->request['TIME_TO']) ?: NewSmile\Config::getScheduleEndTime();
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

    protected function writeSchedule()
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
                'WORK_CHAIR_ID',
                'CLINIC_ID',
                'PATIENT_ID'
            )
        ));

        while ($arSchedule = $rsSchedule->fetch())
        {
            $this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['SCHEDULES'][$arSchedule['TIME']->format('H:i')] = $arSchedule;
            if (empty($this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['DOCTORS'][$arSchedule['UF_DOCTOR_ID']])) {
                $this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['DOCTORS'][$arSchedule['UF_DOCTOR_ID']] = array(
                    'NAME' => $arSchedule['DOCTOR_NAME']
                );
            }

            $isResult = true;
        }
        return $isResult;
    }

	protected function writeVisit()
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
                'STATUS',
                'WORK_CHAIR_ID',
            )
        ));
        while ($arVisit = $rsVisit->Fetch())
        {
            $arResult[] = $arVisit;
            $isResult = true;
        }

        $startTime = $this->arResult['TIME_LINE'][0]->getTimestamp();
        $starTimeStr = $this->arResult['TIME_LINE'][0]->format('H:i');

        foreach ($arResult as $arVisit)
        {
            if(($startTime > $arVisit['TIME_START']->getTimestamp()) && ($startTime < $arVisit['TIME_END']->getTimestamp()))
            {
                $visitKey = $starTimeStr;
            }
            else
            {
                $visitKey = $arVisit['TIME_START']->format('H:i');
            }

            $this->arResult['WORK_CHAIR'][$arVisit['WORK_CHAIR_ID']]['VISITS'][$visitKey] = $arVisit;
        }

        return $isResult;
    }

    protected function writeWorkChair()
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

    protected function writeDoctors()
    {
        $rsDoctor = DoctorTable::getList(array(
            'select' => array(
                'ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'COLOR'
            ),
            'filter' => [
                'CLINIC_ID' => \Mmit\NewSmile\Config::getClinicId()
            ]
        ));
        while ($arDoctor = $rsDoctor->fetch())
        {
            $this->arResult['DOCTORS'][$arDoctor['ID']] = $arDoctor;
        }

        /* запрашиваем main doctors */

        foreach ($this->arResult['WORK_CHAIR'] as &$workChair)
        {
            $workChair['MAIN_DOCTORS'] = [false, false];
        }

        unset($workChair);

        if($this->arResult['DOCTORS'])
        {
            $rsMainDoctors = NewSmile\MainDoctorTable::getList(array(
                'filter' => array(
                    'DOCTOR_ID' => array_keys($this->arResult['DOCTORS']), // так мы косвенно фильтруем по id клиники
                    'DATE' => Date::createFromPhp($this->thisDateTime)
                ),
                'select' => array('DOCTOR_ID', 'WORK_CHAIR_ID', 'SECOND_DAY_HALF'),
            ));

            while($mainDoctor = $rsMainDoctors->fetch())
            {
                $this->arResult['WORK_CHAIR'][$mainDoctor['WORK_CHAIR_ID']]['MAIN_DOCTORS'][(int)$mainDoctor['SECOND_DAY_HALF']] = $mainDoctor['DOCTOR_ID'];
            }
        }
    }

    protected function writePatients()
    {
        $rsPatient = PatientCardTable::getList(array(
            'select' => array(
                'ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'
            )
        ));
        while ($arPatient = $rsPatient->fetch())
        {
            $this->arResult['PATIENTS'][$arPatient['ID']] = $arPatient;
        }
    }

    protected function writeTimeLine()
    {
        $workChairs = $this->arResult['WORK_CHAIR'];
        $firstWorkChair = array_shift($workChairs);

        foreach ($firstWorkChair['SCHEDULES'] as $schedule)
        {
            $this->arResult['TIME_LINE'][] = $schedule['TIME'];
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