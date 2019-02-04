<?


namespace Mmit\NewSmile\Command\Schedule;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\WorkChairTable;
use Mmit\NewSmile;
use Mmit\NewSmile\Date;
use Mmit\NewSmile\Command;
use Mmit\NewSmile\CommandVariable;
use Mmit\NewSmile\CommandVariable\Object;
use Mmit\NewSmile\CommandVariable\Time;
use Mmit\NewSmile\CommandVariable\String;
use Mmit\NewSmile\CommandVariable\Integer;
use Mmit\NewSmile\CommandVariable\ArrayParam;

class GetDaysInfo extends Base
{
    protected $timeFrom;
    protected $timeTo;
    protected $patientsIds;

    public function getDescription()
    {
        return 'Возвращает детальную информацию по расписанию на указанные даты.';
    }

    public function getResultFormat()
    {
        return new Command\ResultFormat([
            (new Object('timeLimits', 'Время начала и время окончания работы клиники', true))->setShape([
                new Time('start', 'Время начала работы', true),
                new Time('end', 'Время окончания работы', true)
            ]),
            new Integer('curServerTimestamp', 'timestamp текущего времени на сервере', true),
            (new Object('commands', 'Информация о доступных командах', true))->setShape([
                (new ArrayParam('<код сущности>', 'коды доступных комманд', true))->setContentType(
                    new String('', '')
                )
            ]),
            (new Object('patients', 'информация о пациентах, записанных на выбранные дни',true))->setShape([
                (new Object('<id пациента>', 'информация о пациенте', true))->setShape([
                    new Integer('id', 'id', true),
                    new String('name', 'имя', true),
                    new String('lastName', 'фамилия', true),
                    new String('secondName', 'отчество', true),
                    new String('phone', 'телефон', true),
                    new String('cardNumber', 'номер карты', true),
                    new String('age', 'возраст', true),
                ])
            ]),
            (new Object('days', 'расписание на запрошенные дни', true))->setShape([
                (new Object('<дата дня>', 'информация о дне', true))->setShape([
                    new String('dateTitle', 'заголовок дня (число и день недели)', true),
                    new CommandVariable\Bool('isCurDay', 'является текущим днём', true),
                    (new ArrayParam('schedule', 'расписание на каждое кресло', true))->setContentType(
                        (new Object('', '', true))->setShape([
                            (new Object('chair', 'информация о кресле', true))->setShape([
                                new Integer('id', 'id', true),
                                new String('name', 'название', true)
                            ]),
                            (new ArrayParam('intervals', 'интервалы расписания', true))->setContentType(
                                (new Object('', '', true))->setShape([
                                    new Integer('DOCTOR_ID', 'id врача', true),
                                    new Time('TIME_START', 'начальное время интервала', true),
                                    new Time('TIME_END', 'конечное время интервала', true),
                                ])
                            ),
                            (new Object('visits', 'приёмы', true))->setShape([
                                (new Object('<начальное время приёма>', 'информация о приёме', true))->setShape([
                                    new Integer('ID', 'id приёма', true),
                                    new Integer('DOCTOR_ID', 'id врача', true),
                                    new Integer('PATIENT_ID', 'id пациента', true),
                                    new Integer('WORK_CHAIR_ID', 'id кресла', true),
                                    new Time('TIME_START', 'начальное время приёма', true),
                                    new Time('TIME_END', 'конечное время приёма', true),
                                    new String('STATUS', 'код статуса приёма', true),
                                ])
                            ])
                        ])
                    )
                ])
            ])
        ]);
    }

    public function getParamsMap()
    {
        return [
            (new ArrayParam('dates', 'даты запрашиваемых дней', false, [date('Y-m-d')]))->setContentType(
                new NewSmile\CommandVariable\Date('', '')
            )
        ];
    }

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
                'WORK_CHAIR_ID'
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