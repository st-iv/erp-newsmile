<?


namespace Mmit\NewSmile\Command\Schedule;

use Bitrix\Main\Diag\Debug;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\WorkChairTable;
use Mmit\NewSmile;
use Mmit\NewSmile\Date;
use Mmit\NewSmile\Command;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\DB;
use Bitrix\Main\ORM\Fields\ExpressionField;

class GetDaysInfo extends Base
{
    protected static $name = 'Получить расписание на день';
    
    protected $timeFrom;
    protected $timeTo;

    protected $patientsIds;

    protected function doExecute()
    {
        $timeLimits = [
            'start' => NewSmile\Config::getScheduleStartTime(),
            'end' => NewSmile\Config::getScheduleEndTime(),
        ];
        
        $this->timeFrom = $timeLimits['start'];
        $this->timeTo = $timeLimits['end'];

        $this->result = [
            'timeLimits' => $timeLimits,
            'curServerTimestamp' => time(),
            'commands' => $this->getAllowedCommands(),
        ];

        $patientsIds = [];
        $curDate = date('Y-m-d');

        $visits = $this->getVisits();
        $rawSchedule = $this->getSchedule();
        $chairs = $this->getChairs();

        foreach ($this->params['dates'] as $date)
        {
            $daySchedule = [];

            foreach ($chairs as $chairId => $chair)
            {
                if (!$rawSchedule[$date][$chairId])
                    continue;

                $chairVisits = $visits[$date][$chairId];

                foreach ($chairVisits as $visit)
                {
                    $patientsIds[] = $visit['PATIENT_ID'];
                }

                $daySchedule[] = [
                    'chair' => $chair,
                    'intervals' => $this->getDoctorsIntervals($rawSchedule[$date][$chairId]),
                    'visits' => $chairVisits ?: []
                ];
            }

            $this->result['days'][$date] = [
                'schedule' => $daySchedule,
                'dateTitle' => Date\Helper::date('l_ru - j F_ru_gen', strtotime($date)),
                'isCurDay' => ($date == $curDate)
            ];
        }

        $this->result['patients'] = $this->getPatients($patientsIds);
    }

    public function getParamsMap()
    {
        return [
            new NewSmile\CommandVariable\ArrayParam('dates', 'даты', false, [date('Y-m-d')])
        ];
    }

    protected function getSchedule()
    {
        $result = [];

        $rsSchedule = NewSmile\ScheduleTable::getList(array(
            'order' => array(
                'TIME' => 'ASC'
            ),
            'filter' => [
                'CLINIC_ID' => NewSmile\Config::getClinicId(),
                'DATE' => $this->params['dates']
            ],
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
            $result[$arSchedule['TIME']->format('Y-m-d')][$arSchedule['WORK_CHAIR_ID']][$arSchedule['TIME']->format('H:i')] = $arSchedule;
        }

        return $result;
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

        $bitrixDates = array_map(function($date)
        {
            return new \Bitrix\Main\Type\Date($date, 'Y-m-d');
        }, $this->params['dates']);

        $rsVisit = NewSmile\Visit\VisitTable::getList(array(
            'order' => array(
                'TIME_START' => 'ASC'
            ),
            'filter' => array(
                'DATE_START' => $bitrixDates,
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
                'DATE_START'
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

            $result[ $visit['DATE_START']->format('Y-m-d') ][$visit['WORK_CHAIR_ID']][$visitKey] = $visit;
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
                'age' => $patient['PERSONAL_BIRTHDAY'] ? Date\Helper::getAge($patient['PERSONAL_BIRTHDAY']) : '',
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