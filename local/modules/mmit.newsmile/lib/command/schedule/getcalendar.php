<?

namespace Mmit\NewSmile\Command\Schedule;


use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\DB;
use Mmit\NewSmile\Command;
use Mmit\NewSmile\ScheduleTable;
use Mmit\NewSmile;
use Bitrix\Main\Entity\ExpressionField;
use Mmit\NewSmile\CommandVariable;

class GetCalendar extends Command\Base
{
    /**
     * @var \DateTime
     */
    protected $dateFrom;

    /**
     * @var \DateTime
     */
    protected $dateTo;

    public function getDescription()
    {
        return 'Возвращает общую информацию по расписанию клиники: 
                общее количество рабочих часов, количество занятых часов и количество записанных пациентов';
    }

    public function getResultFormat()
    {
        return new Command\ResultFormat([
            new CommandVariable\Date('dateFrom', 'Начальная дата выборки', true),
            new CommandVariable\Date('dateTo', 'Конечная дата выборки', true),
            (new CommandVariable\Object(
                'dateData',
                'Информация по всем дням расписания, попавшим в выборку',
                true
            ))->setShape([
                (new CommandVariable\Object('<дата>', 'Информация по конкретному дню'))->setShape([
                    new CommandVariable\Integer('generalTime', 'Доступное время (в минутах)', true),
                    new CommandVariable\Integer('engagedTime', 'Занятое время (в минутах)', true),
                    new CommandVariable\Bool('isAvailable', 'На день составлено расписание', true),
                    new CommandVariable\Bool('isCurrent', 'День является текущим', true),
                    new CommandVariable\Bool('isEmpty', 'День является пустым', true),
                    new CommandVariable\Integer('patientsCount', 'Количество запсанных пациентов', true),
                ])
            ]),
        ]);
    }

    protected function doExecute()
    {
        $this->initDateInterval();
        $this->result['dateFrom'] = $this->dateFrom->format('Y-m-d');
        $this->result['dateTo'] = $this->dateTo->format('Y-m-d');

        $rsSchedule = ScheduleTable::getList(array(
            'filter' => $this->getFilter(),
            'select' => array('ID', 'PATIENT_ID', 'TIME', 'DURATION', 'DATE'),
            'order' => [
                'DATE' => 'asc',
                'WORK_CHAIR_ID' => 'asc',
                'TIME' => 'asc'
            ]
        ));

        $counter = array();
        $schedules = [];

        while ($schedule = $rsSchedule->fetch())
        {
            $schedules[$schedule['DATE']][] = $schedule;
        }

        $timeFrom = new \DateTime($this->params['timeFrom']);
        $isHalfTimeFrom = $this->params['timeFrom'] && NewSmile\Scheduler::isHalfTime($timeFrom);
        $isHalfTimeTo = $this->params['timeTo'] && NewSmile\Scheduler::isHalfTime(new \DateTime($this->params['timeTo']));

        foreach($schedules as $date => $dateSchedules)
        {
            $dateSchedulesCount = count($dateSchedules);
            $timeFrom->modify($date);

            foreach ($dateSchedules as $index => $schedule)
            {
                if($schedule['TIME'])
                {
                    if($isHalfTimeFrom && !$index)
                    {
                        // если установлен фильтр по времени и начальное время является половинным (15 или 45 минут),
                        /*
                         * Для корректного подсчета доступного и занятого времени фильтр по времени может быть "сдвинут" на 15 минут,
                         * чтобы захватить все нужные интервалы. Поэтому в этом условии проверяем - не нужно ли пропустить начальный интервал
                         */
                        if(NewSmile\Date\Helper::isBefore($schedule['TIME'], $timeFrom))
                        {
                            if($schedule['DURATION'] == ScheduleTable::STANDARD_INTERVAL / 120)
                            {
                                continue;
                            }
                            else
                            {
                                // Если попали сюда, значит половинным фильтром по времени был разделен стандартный интервал расписания.
                                // В таком случае нужно учитывать только половину от этого интервала.
                                $schedule['DURATION'] = ScheduleTable::STANDARD_INTERVAL / 120;
                            }
                        }
                    }

                    if($isHalfTimeTo && ($dateSchedulesCount === ($index + 1)))
                    {
                        // если установлен фильтр по времени и конечное время является половинным (15 или 45 минут),
                        // то последний выбранный интервал должен считаться как половинный
                        $schedule['DURATION'] = ScheduleTable::STANDARD_INTERVAL / 120;
                    }

                    /**
                     * @var DateTime $timeObject
                     */
                    $timeObject = $schedule['TIME'];
                    $date = $timeObject->format('Y-m-d');

                    $counter[$date]['GENERAL'] += $schedule['DURATION'];

                    if($schedule['PATIENT_ID'])
                    {
                        $counter[$date]['ENGAGED'] += $schedule['DURATION'];
                        $counter[$date]['PATIENTS'][$schedule['PATIENT_ID']] = true;
                    }
                }
            }
        }

        $availability = NewSmile\Scheduler::checkAvailability(clone $this->dateFrom, clone $this->dateTo);
        $curDate = date('Y-m-d');

        foreach ($availability as $date => $isAvailable)
        {
            $countInfo = $counter[$date];
            $dateInfo = [
                'isAvailable' => $isAvailable,
                'isEmpty' => !isset($countInfo),
                'isCurrent' => $curDate == $date
            ];

            if(isset($countInfo))
            {
                $dateInfo['generalTime'] = $countInfo['GENERAL'];
                $dateInfo['engagedTime'] = $countInfo['ENGAGED'];
                $dateInfo['patientsCount'] = count($countInfo['PATIENTS']);
            }

            $this->result['dateData'][$date] = $dateInfo;
        }
    }

    protected function initDateInterval()
    {
        $this->dateFrom = new \DateTime($this->params['dateFrom']);
        $this->dateFrom->modify('Monday this week');
        $this->params['weeksCount'] = (int)$this->params['weeksCount'];

        if($this->params['dateTo'])
        {
            $this->dateTo = new \DateTime($this->params['dateTo']);
        }
        else
        {
            $this->dateTo = clone $this->dateFrom;
            $this->dateTo->modify('+' . $this->params['weeksCount'] . ' weeks');
            $this->dateTo->modify('Sunday this week');
        }
    }

    protected function getFilter()
    {
        /**
         * @var \Bitrix\Main\ORM\Query\Filter\ConditionTree $filter
         */
        $filter = Query::filter();

        $filter->where('TIME', '>=', DateTime::createFromPhp($this->dateFrom));
        $filter->where('TIME', '<', DateTime::createFromPhp($this->dateTo));

        if (!empty($this->params['timeFrom']))
        {
            $timeFrom = new \DateTime(urldecode($this->params['timeFrom']));

            if( NewSmile\Scheduler::isHalfTime(new \DateTime($this->params['timeFrom'])) )
            {
                $timeFrom->modify('-' . ScheduleTable::STANDARD_INTERVAL / 120 . ' minute');
            }

            $filter->where(
                new ExpressionField('TIME_SECONDS', 'TIME_TO_SEC(%s)','TIME'),
                '>=',
                new DB\SqlExpression('TIME_TO_SEC(?)', $timeFrom->format('H:i:00'))
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

        if($doctorId)
        {
            $filter->where('DOCTOR_ID', $doctorId);
        }
        else
        {
            $filter->whereNot('DOCTOR_ID', false);
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

        return $filter;
    }

    public function getParamsMap()
    {
        return [
            new NewSmile\CommandVariable\Date('dateFrom', 'начальная дата', false, date('Y-m-d')),
            new NewSmile\CommandVariable\Date('dateTo', 'конечная дата'),
            new NewSmile\CommandVariable\Integer('weeksCount', 'количество запрашиваемых недель', false, 8
            ),
            new NewSmile\CommandVariable\Time('timeFrom', 'начальное время выборки'),
            new NewSmile\CommandVariable\Time('timeTo', 'конечное время выборки'),
            new NewSmile\CommandVariable\Integer('doctor', 'id врача'),
            new NewSmile\CommandVariable\String('specialization', 'код специальности')
        ];
    }
}