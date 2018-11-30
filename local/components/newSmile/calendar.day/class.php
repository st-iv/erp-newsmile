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

class CalendarDayComponent extends NewSmile\Component\AdvancedComponent
{
    protected $thisDate = '';
    protected $patientsIds = [];

    /**
     * @var \DateTime
     */
    protected $thisDateTime;
    /**
     * @var NewSmile\Access\Controller
     */
    protected $accessController;

    protected function prepareParams(array $arParams)
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

        foreach ($this->arResult['WORK_CHAIR'] as &$workChair)
        {
            $workChair['DOCTORS_SCHEDULE'] = $workChair['INTERVALS'] ? $this->getDoctorsIntervals($workChair['INTERVALS']) : [];
            unset($workChair['INTERVALS']);
        }

        unset($workChair);


        $this->arResult['SCHEDULE_START_TIME'] = NewSmile\Config::getScheduleStartTime();
        $this->arResult['SCHEDULE_END_TIME'] = NewSmile\Config::getScheduleEndTime();

        $this->arResult['START_TIME'] = urldecode($this->request['TIME_FROM']) ?: $this->arResult['SCHEDULE_START_TIME'];
        $this->arResult['END_TIME'] = urldecode($this->request['TIME_TO']) ?: $this->arResult['SCHEDULE_END_TIME'];
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
                'DOCTOR_ID',
                'WORK_CHAIR_ID',
                'CLINIC_ID',
                'PATIENT_ID',
                'DURATION'
            )
        ));

        while ($arSchedule = $rsSchedule->fetch())
        {
            $this->arResult['WORK_CHAIR'][$arSchedule['WORK_CHAIR_ID']]['INTERVALS'][$arSchedule['TIME']->format('H:i')] = $arSchedule;
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
                'PATIENT_ID',
                'DOCTOR_ID',
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
            $this->patientsIds[] = $arVisit['PATIENT_ID'];
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
        $rsPatient = PatientCardTable::getList([
            'filter' => [
                'ID' => $this->patientsIds
            ]
        ]);
        while ($arPatient = $rsPatient->fetch())
        {
            $this->arResult['PATIENTS'][$arPatient['ID']] = $arPatient;
        }
    }

    protected function writeTimeLine()
    {
        $workChairs = $this->arResult['WORK_CHAIR'];
        $firstWorkChair = array_shift($workChairs);

        foreach ($firstWorkChair['INTERVALS'] as $schedule)
        {
            $this->arResult['TIME_LINE'][] = $schedule['TIME'];
        }
    }

    protected function getDoctorsIntervals(array $intervals)
    {
        $unitedIntervals = [];
        $prevDoctorId = null;
        $intervalStartTime = null;
        $counter = 0;
        $schedulesCount = count($intervals);

        /* подготовка doctors (заполнение массива workTime) */

        foreach ($intervals as $interval)
        {
            $counter++;
            $isLastItem = ($counter == $schedulesCount);

            if($isLastItem || ($interval['DOCTOR_ID'] !== $prevDoctorId))
            {
                if($prevDoctorId)
                {
                    if($isLastItem)
                    {
                        $intervalEndTime = new \DateTime();
                        $intervalEndTime->setTimestamp($interval['TIME']->getTimestamp());
                        $intervalEndTime->modify('+' . $interval['DURATION'] . ' minute');
                    }
                    else
                    {
                        $intervalEndTime = clone $interval['TIME'];
                    }

                    // записываем интервал

                    $unitedInterval = [
                        'DOCTOR_ID' => $prevDoctorId,
                        'TIME_START' => $intervalStartTime->format('H:i'),
                        'TIME_END' => $intervalEndTime->format('H:i')
                    ];

                    $unitedIntervals[] = $unitedInterval;
                }

                $intervalStartTime = $interval['TIME'];
            }

            $prevDoctorId = $interval['DOCTOR_ID'];
        }

        return $unitedIntervals;
    }

    protected function getAllowedCommands()
    {
        $fullCommandsList = [
            new NewSmile\Command\Schedule\ChangeDoctor()
        ];

        //$this->arResult['COMMANDS'] =
    }

	public function execute()
	{
		try
		{
            if (!Loader::includeModule('mmit.newSmile')) die();

            $this->accessController = NewSmile\Application::getInstance()->getAccessController();
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