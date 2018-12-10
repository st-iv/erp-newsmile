<?


namespace Mmit\NewSmile\Command\Schedule;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\WorkChairTable;
use Mmit\NewSmile;
use Mmit\NewSmile\Date;
use Mmit\NewSmile\Command;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\DB;
use Bitrix\Main\ORM\Fields\ExpressionField;

class GetDayInfo extends Base
{
    protected static $name = 'Получить расписание на день';
    
    protected $timeFrom;
    protected $timeTo;

    protected $patientsIds;

    protected function doExecute()
    {
        $curDateTime = new \DateTime($this->params['date']);

        $timeLimits = [
            'start' => NewSmile\Config::getScheduleStartTime(),
            'end' => NewSmile\Config::getScheduleEndTime(),
        ];
        
        $this->timeFrom = $this->params['timeFrom'] ?: $timeLimits['start'];
        $this->timeTo = $this->params['timeTo'] ?: $timeLimits['end'];

        $this->result = [
            'timeLimits' => $timeLimits,

            'dateTitle' => Date\Helper::date('l_ru - j F_ru_gen', $curDateTime->getTimestamp()),

            'startTime' => $this->timeFrom,
            'endTime' => $this->timeTo,

            'curServerTimestamp' => time(),
            'isCurDay' => ($this->params['date'] == date('Y-m-d'))
        ];

        $patientsIds = [];
        $schedule = [];

        $visits = $this->getVisits();
        $rawSchedule = $this->getSchedule();

        foreach ($this->getChairs() as $chairId => $chair)
        {
            if(!$rawSchedule[$chairId]) continue;

            $chairVisits = $visits[$chairId];

            foreach ($chairVisits as $visit)
            {
                $patientsIds[] = $visit['PATIENT_ID'];
            }

            $schedule[] = [
                'chair' => $chair,
                'intervals' => $this->getDoctorsIntervals($rawSchedule[$chairId]),
                'visits' => $visits[$chairId] ?: []
            ];
        }

        $this->result['schedule'] = $schedule;
        $this->result['doctors'] = $this->getDoctors();
        $this->result['patients'] = $this->getPatients($patientsIds);
        $this->result['commands'] = $this->getAllowedCommands();
    }

    public function getParamsMap()
    {
        return [
            'date' => [
                'TITLE' => 'дата',
                'REQUIRED' => true,
                'DEFAULT' => date('Y-m-d')
            ],
            'timeFrom' => [
                'TITLE' => 'начальное время выборки',
            ],
            'timeTo' => [
                'TITLE' => 'конечное время выборки',
            ],
            'doctor' => [
                'TITLE' => 'id врача',
            ],
            'specialization' => [
                'TITLE' => 'код специальности'
            ]
        ];
    }

    protected function getSchedule()
    {
        $result = [];

        $rsSchedule = NewSmile\ScheduleTable::getList(array(
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
            $result[$arSchedule['WORK_CHAIR_ID']][$arSchedule['TIME']->format('H:i')] = $arSchedule;
        }

        return $result;
    }

    protected function getScheduleFilter()
    {
        /**
         * @var \Bitrix\Main\ORM\Query\Filter\ConditionTree $filter
         */
        $filter = Query::filter();//$this->arParams['FILTER'];

        $thisDate = new \DateTime($this->params['date']);
        $tomorrowDate = clone $thisDate;
        $tomorrowDate->modify('tomorrow');

        $filter->whereBetween('TIME', \Bitrix\Main\Type\Date::createFromPhp($thisDate), \Bitrix\Main\Type\Date::createFromPhp($tomorrowDate));

        if (!empty($this->params['timeFrom']))
        {
            $filter->where(
                new ExpressionField('TIME_SECONDS', 'TIME_TO_SEC(%s)','TIME'),
                '>=',
                new DB\SqlExpression('TIME_TO_SEC(?)', urldecode($this->params['timeFrom']))
            );
        }

        if (!empty($this->params['timeTo']))
        {
            $filter->where(
                new ExpressionField('TIME_SECONDS', 'TIME_TO_SEC(%s)','TIME'),
                '<',
                new DB\SqlExpression('TIME_TO_SEC(?)', urldecode($this->params['timeTo']))
            );
        }

        $doctorId = (int)$this->params['doctor'];

        if ($doctorId)
        {
            $filter->where('DOCTOR_ID', $doctorId);
        }

        if($this->params['specialization'])
        {
            $specSubQuery = new Query(NewSmile\DoctorSpecializationTable::getEntity());
            $specSubQuery->setFilter(array(
                'SPECIALIZATION' => $this->params['specialization']
            ));
            $specSubQuery->setSelect(array('DOCTOR_ID'));

            $filter->whereIn('DOCTOR_ID', $specSubQuery);
        }

        $filter->where('CLINIC_ID', NewSmile\Config::getClinicId());

        return $filter;
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

    protected function getVisits()
    {
        $result = [];

        $rsVisit = NewSmile\VisitTable::getList(array(
            'order' => array(
                'TIME_START' => 'ASC'
            ),
            'filter' => array(
                'DATE_START' => new \Bitrix\Main\Type\Date($this->params['date'], 'Y-m-d'),
                'CLINIC_ID' => NewSmile\Config::getClinicId()
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

        $visits = $rsVisit->fetchAll();
        
        $timeFromDateTime = new \DateTime($this->timeFrom);

        foreach ($visits as $visit)
        {

            if(Date\Helper::isBefore($visit['TIME_START'], $timeFromDateTime) && Date\Helper::isAfter($visit['TIME_END'], $timeFromDateTime))
            {
                $visitKey = $this->timeFrom;
            }
            else
            {
                $visitKey = $visit['TIME_START']->format('H:i');
            }

            $visit['TIME_START'] = $visit['TIME_START']->format('H:i');
            $visit['TIME_END'] = $visit['TIME_END']->format('H:i');

            $result[$visit['WORK_CHAIR_ID']][$visitKey] = $visit;
            $this->patientsIds[] = $visit['PATIENT_ID'];
        }

        return $result;
    }

    protected function getChairs()
    {
        $result = [];

        $rsWorkChair = WorkChairTable::getList([
            'filter' => [
                'CLINIC_ID' => NewSmile\Config::getClinicId()
            ]
        ]);
        while ($chair = $rsWorkChair->Fetch())
        {
            $result[$chair['ID']] = [
                'id' => $chair['ID'],
                'name' => $chair['NAME']
            ];
        }

        return $result;
    }

    protected function getDoctors()
    {
        $result = [];

        $rsDoctor = NewSmile\DoctorTable::getList(array(
            'select' => array(
                'ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'COLOR'
            ),
            'filter' => [
                'CLINIC_ID' => NewSmile\Config::getClinicId()
            ]
        ));
        while ($doctor = $rsDoctor->fetch())
        {
            $result[$doctor['ID']] = [
                'id' => $doctor['ID'],
                'fio' => NewSmile\Helpers::getFio($doctor),
                'color' => $doctor['COLOR']
            ];
        }

        return $result;
    }

    protected function getPatients(array $ids)
    {
        $result = [];

        $rsPatient = NewSmile\PatientCardTable::getList([
            'filter' => [
                'ID' => $ids
            ],
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'PERSONAL_PHONE', 'NUMBER', 'PERSONAL_BIRTHDAY']
        ]);
        while ($patient = $rsPatient->fetch())
        {
            $result[$patient['ID']] = [
                'id' => $patient['ID'],
                'name' => $patient['NAME'],
                'lastName' => $patient['LAST_NAME'],
                'secondName' => $patient['SECOND_NAME'],
                'phone' => $patient['PERSONAL_PHONE'],
                'cardNumber' => $patient['NUMBER'],
                'age' => Date\Helper::getAge($patient['PERSONAL_BIRTHDAY']),
            ];
        }

        return $result;
    }

    protected function getAllowedCommands()
    {
        $result = [];

        $fullCommandsList = [
            Command\Schedule\ChangeDoctor::class,
            Command\Visit\Add::class
        ];


        foreach ($fullCommandsList as $commandClass)
        {
            /**
             * @var Command\Base $commandClass
             */

            if($commandClass::isAvailableForUser())
            {
                $result[$commandClass::getEntityCode()][] = $commandClass::getCode();
            }
        }

        return $result;
    }
}