<?

namespace Mmit\NewSmile;


use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Date\Helper;
use Mmit\NewSmile;

/**
 * Выполняет массовые операции с интервалами расписания, делит и объединяет интервалы
 * Class Scheduler
 * @package Mmit\NewSmile
 */
class Scheduler
{
    protected $date;
    protected $schedulesTable = [];
    protected $deleteIntervals = [];

    public function __construct($date = null)
    {
        if($date)
        {
            $date = Helper::getDateTime($date);
        }
        else
        {
            $date = new \DateTime();
        }

        $this->date = $date;
    }

    /**
     * Обновляет указанный диапазон расписания. Автоматически разбивает и объединяет интервалы при необходимости
     *
     * @param string $startTime - начало диапазона в формате H:i
     * @param string $endTime - конец диапазона в формате H:i
     * @param int $workChairId - id рабочего кресла
     * @param array $fields - новые значения полей интервалов, входящих в диапазон.
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     *
     * @return array массив общих значений полей обновлённых интервалов расписания
     */
    public function updateByTime($startTime, $endTime, $workChairId, $fields)
    {
        unset($fields['TIMESTAMP_X']);
        unset($fields['ID']);
        unset($fields['DURATION']);
        unset($fields['TIME']);
        unset($fields['WORK_CHAIR_ID']);

        $startDateTime = Helper::getDateTime($startTime);
        $startDateTime->modify($this->date->format('d.m.Y'));

        $queryStartDateTime = clone $startDateTime;
        $queryStartTime = '';

        if (static::isHalfTime($startDateTime))
        {
            // если начальный интервал половинный (в $startTime указано 15 или 45 минут), то этот интервал может не существовать
            // (если родительский интервал не был разделен). Поэтому отодвигаем начальное время на половину начального интервала
            $queryStartDateTime->modify('-15 minute');
            $queryStartTime = $queryStartDateTime->format('H:i');
        }

        $endDateTime = Helper::getDateTime($endTime);
        $endDateTime->modify($this->date->modify('d.m.Y'));

        $dbCurrentIntervals = ScheduleTable::getList([
            'filter' => [
                '>=TIME' => DateTime::createFromPhp($queryStartDateTime),
                '<TIME' => DateTime::createFromPhp($endDateTime)
            ],
            'order' => [
                'TIME' => 'asc'
            ]
        ]);

        // составляем виртуальную таблицу интервалов в текущем их состоянии

        while($currentInterval = $dbCurrentIntervals->fetch())
        {
            $intervalTime = $currentInterval['TIME']->format('H:i');
            $this->schedulesTable[$currentInterval['WORK_CHAIR_ID']][$intervalTime] = $currentInterval;
        }

        $this->splitIntervals($startDateTime, $endDateTime, $workChairId);


        $exceptFromUpdate = [];
        if($queryStartTime)
        {
            // если начальное время было сдвинуто - не обновляем начальный интервал
            $exceptFromUpdate[$queryStartTime] = true;
        }

        if(static::isHalfTime($endDateTime))
        {
            $exceptTime = clone $endDateTime;
            $exceptFromUpdate[$exceptTime->format('H:i')] = true;
        }

        $updatedIntervals = [];

        // накатываем новые значения интервалов для текущего рабочего кресла
        foreach ($this->schedulesTable[$workChairId] as $intervalTime => &$interval)
        {
            if(!$exceptFromUpdate[$intervalTime])
            {
                $interval = array_merge($interval, $fields);
                $updatedIntervals[] = $interval;
            }
        }

        $updatedGeneralFields = [];

        if($updatedIntervals)
        {
            if(count($updatedIntervals) == 1)
            {
                $updatedGeneralFields = $updatedIntervals[0];
            }
            else
            {
                $updatedGeneralFields = call_user_func_array('array_intersect_assoc', $updatedIntervals);
            }
        }

        $this->cleanIntervals();

        $this->save();

        return $updatedGeneralFields;
    }

    /**
     * Рабивает интервалы, которые попадают на начало и конец указанного временного диапазона
     * @param \DateTime|Date|string $startDateTime - начало временного диапазона
     * @param \DateTime|Date|string $endDateTime - конец временного диапазона
     * @param $workChairId - id рабочего кресла
     */
    public function splitIntervals($startDateTime, $endDateTime, $workChairId)
    {
        $startDateTime = NewSmile\Date\Helper::getDateTime($startDateTime);
        $endDateTime = NewSmile\Date\Helper::getDateTime($endDateTime);

        $startTime = $startDateTime->format('H:i');
        $endTime = $endDateTime->format('H:i');

        $curIntervals =& $this->schedulesTable[$workChairId];
        $standardIntervalMinutes = ScheduleTable::STANDARD_INTERVAL / 60;

        $timeLine = array_keys($curIntervals);
        $startIntervalTime = $timeLine[0];
        $lastIntervalTime = $timeLine[count($timeLine) - 1];

        //начальный интервал
        if(static::isHalfTime($startDateTime))
        {
            if($curIntervals[$startTime])
            {
                // если в виртуальной таблице нашелся начальный половинный интервал, значит начальный интервал уже разделен
                // поэтому первый половинный интервал можно удалить из виртуальной таблицы, его обновлять не нужно

                foreach ($this->schedulesTable as &$workChairSchedule)
                {
                    array_shift($workChairSchedule);
                }
            }
            else
            {
                // иначе разбиваем начальный интервал для всех кресел
                foreach ($this->schedulesTable as &$workChairSchedule)
                {
                    $workChairSchedule[$startIntervalTime]['DURATION'] = $standardIntervalMinutes / 2;

                    $newInterval = $workChairSchedule[$startIntervalTime];
                    $newInterval['TIME'] = DateTime::createFromPhp($startDateTime);
                    unset($newInterval['ID']);
                    unset($newInterval['TIMESTAMP_X']);

                    $workChairSchedule[$startTime] = $newInterval;
                }
            }

            unset($workChairSchedule);
        }

        // конечный интервал
        if(static::isHalfTime($endDateTime))
        {
            if($curIntervals[$lastIntervalTime]['DURATION'] != $standardIntervalMinutes / 2)
            {
                // если endTime было указано половинное (15 или 45 минут), значит последний интервал должен быть половинным
                // если это не так - разделяем последний интервал для всех кресел

                foreach ($this->schedulesTable as &$workChairSchedule)
                {
                    $workChairSchedule[$lastIntervalTime]['DURATION'] = $standardIntervalMinutes / 2;

                    $newInterval = $workChairSchedule[$lastIntervalTime];
                    $newInterval['TIME'] = DateTime::createFromPhp($endDateTime);
                    unset($newInterval['ID']);
                    unset($newInterval['TIMESTAMP_X']);

                    $workChairSchedule[$endTime] = $newInterval;
                }

                unset($workChairSchedule);
            }
        }
    }

    /**
     * Объединяет одинаковые соседние половинные интервалы
     */
    protected function cleanIntervals()
    {
        $halfMinutes = ScheduleTable::STANDARD_INTERVAL / 120;
        $uniteIntervals = [];

        foreach ($this->schedulesTable as $workChairId => $workChairSchedule)
        {
            foreach ($workChairSchedule as $intervalTimeStr => $interval)
            {
                // пропускаем все интервалы, кроме тех, которые являются первыми половинными в паре
                if(($interval['DURATION'] != $halfMinutes) || static::isHalfTime($interval['TIME'])) continue;

                // получение второго половинного интервала
                $nextIntervalTime = new \DateTime;
                $nextIntervalTime->setTimestamp($interval['TIME']->getTimestamp());
                $nextIntervalTime->modify('+' . $halfMinutes . ' minute');
                $nextIntervalTimeStr = $nextIntervalTime->format('H:i');

                $nextInterval = $workChairSchedule[$nextIntervalTimeStr];

                // Сравнение интервалов и "голосование" за объединение. Интервалы нужно объединить, если они одинаковы.
                // Но тк интервал можно объединить только для всех кресел сразу, нужно чтобы объединение было возможно
                // на всех креслах

                unset($nextInterval['ID']);
                unset($nextInterval['TIMESTAMP_X']);
                unset($nextInterval['TIME']);

                unset($interval['ID']);
                unset($interval['TIMESTAMP_X']);
                unset($interval['TIME']);


                if($nextInterval == $interval)
                {
                    if(!isset($uniteIntervals[$intervalTimeStr]))
                    {
                        $uniteIntervals[$intervalTimeStr] = $nextIntervalTimeStr;
                    }
                }
                else
                {
                    $uniteIntervals[$intervalTimeStr] = false;
                }
            }
        }

        // объединение интервалов
        foreach ($uniteIntervals as $firstHalfTime => $secondHalfTime)
        {
            if(!$secondHalfTime) continue;

            foreach ($this->schedulesTable as $workChairId => &$workChairSchedule)
            {
                $this->schedulesTable[$workChairId][$firstHalfTime]['DURATION'] *= 2;

                if($this->schedulesTable[$workChairId][$secondHalfTime]['ID'])
                {
                    $this->deleteIntervals[] = $this->schedulesTable[$workChairId][$secondHalfTime]['ID'];
                }

                unset($this->schedulesTable[$workChairId][$secondHalfTime]);
            }

            unset($workChairSchedule);
        }
    }


    /**
     * Сохраняет в бд внесенные изменения
     */
    public function save()
    {
        foreach ($this->schedulesTable as $workChairSchedule)
        {
            foreach ($workChairSchedule as $interval)
            {
                $newFields = $interval;
                unset($newFields['ID']);
                unset($newFields['TIMESTAMP_X']);

                if($interval['ID'])
                {
                    ScheduleTable::update($interval['ID'], $newFields);
                }
                else
                {
                    ScheduleTable::add($newFields);
                }
            }
        }

        $this->schedulesTable = [];

        foreach ($this->deleteIntervals as $intervalId)
        {
            ScheduleTable::delete($intervalId);
        }

        $this->deleteIntervals = [];
    }

    /**
     * Проверяет, является ли время началом "половинного" интервала (15 или 45 минут)
     * @param \DateTime | DateTime $time
     *
     * @return bool
     */
    public static function isHalfTime($time)
    {
        $standardIntervalMinutes = ScheduleTable::STANDARD_INTERVAL / 60;
        return (int)$time->format('i') % $standardIntervalMinutes == $standardIntervalMinutes / 2;
    }


    public static function getDoctorsSchedule()
    {
        $dbSchedule = ScheduleTemplateTable::getList([
            'order' => [
                'CLINIC_ID' => 'asc',
                'WORK_CHAIR_ID' => 'asc',
                'TIME' => 'asc'
            ]
        ]);

        $result = [];
        $intervalStart = null;
        $prevSchedule = null;

        while($schedule = $dbSchedule->fetch())
        {
            if(!$prevSchedule || ($schedule['DOCTOR_ID'] != $prevSchedule['DOCTOR_ID']))
            {
                if($intervalStart && $prevSchedule['DOCTOR_ID'])
                {
                    $intervalEnd = Helper::getDateTime($prevSchedule['TIME']);
                    $intervalEnd->modify('add +' . $prevSchedule['DURATION'] . ' minute');
                    $isEvenWeek = ScheduleTemplateTable::isEvenWeek(Helper::getDateTime($intervalStart));

                    $weekDayIndex = (int)$intervalStart->format('N');
                    $parity = ($isEvenWeek ? 'even' : 'odd');

                    $strInterval = $intervalStart->format('H:i') . '-' . $intervalEnd->format('H:i');

                    $result[$prevSchedule['DOCTOR_ID']][$parity][$weekDayIndex][] = $strInterval;
                }

                if($schedule['DOCTOR_ID'])
                {
                    $intervalStart = $schedule['TIME'];
                }
            }

            $prevSchedule = $schedule;
        }

        return $result;
    }

    public static function getIntervals($parameters = [])
    {
        /*$parameters['order'] = [
            'CLINIC_ID' => 'asc',
            'WORK_CHAIR_ID' => 'asc',
            'TIME' => 'asc',
        ];

        $parameters['select'][] = 'TIME';

        $dbSchedule = ScheduleTable::getList($parameters);

        while($schedule = $dbSchedule->fetch())
        {

        }*/
    }

    /**
     * Проверяет, составлено ли расписание на даты из указанного интервала
     * @param \DateTime $startDate - начальная дата интервала
     * @param \DateTime $endDate - конечная дата интервала. Если не указана, то будет проверена только одна дата $startDate
     */
    public static function checkAvailability(\DateTime $startDate, \DateTime $endDate = null)
    {
        if($endDate)
        {
            $endDate->modify('+1 day');
            $filter = [
                '>=TIME' => DateTime::createFromPhp($startDate),
                '<TIME' => DateTime::createFromPhp($endDate)
            ];
        }
        else
        {
            $filter = [
                'TIME' => DateTime::createFromPhp($startDate)
            ];
        }

        $dbSchedule = ScheduleTable::getList([
            'filter' => $filter,
            'select' => ['DATE']
        ]);

        $filledDates = [];

        while($schedule = $dbSchedule->fetch())
        {
            $filledDates[$schedule['DATE']] = true;
        }

        $iterDate = clone $startDate;
        $result = [];

        while(!Helper::isAfter($iterDate, $endDate))
        {
            $strIterDate = $iterDate->format('Y-m-d');
            $result[$strIterDate] = isset($filledDates[$strIterDate]);
            $iterDate->modify('+1 day');
        }

        return $result;
    }
}