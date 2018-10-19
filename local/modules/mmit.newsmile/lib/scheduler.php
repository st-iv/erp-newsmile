<?

namespace Mmit\NewSmile;


use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

class Scheduler
{
    protected $date;
    protected $schedulesTable = [];
    protected $deleteIntervals = [];

    public function __construct(\DateTime $date = null)
    {
        if(!$date)
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
     */
    public function updateByTime($startTime, $endTime, $workChairId, $fields)
    {
        unset($fields['TIMESTAMP_X']);
        unset($fields['ID']);
        unset($fields['DURATION']);
        unset($fields['TIME']);
        unset($fields['WORK_CHAIR_ID']);

        $this->date->modify($startTime);
        $startDateTime = clone $this->date->modify($startTime);
        $queryStartDateTime = clone $startDateTime;
        $queryStartTime = '';

        if (static::isHalfTime($startDateTime))
        {
            // если начальный интервал половинный (в $startTime указано 15 или 45 минут), то этот интервал может не существовать
            // (если родительский интервал не был разделен). Поэтому отодвигаем начальное время на половину начального интервала
            $queryStartDateTime->modify('-15 minute');
            $queryStartTime = $queryStartDateTime->format('H:i');
        }

        $endDateTime = clone $this->date->modify($endTime);

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
            $exceptFromUpdate[$queryStartTime] = true;
        }

        if(static::isHalfTime($endDateTime))
        {
            $exceptTime = clone $endDateTime;
            $exceptFromUpdate[$exceptTime->format('H:i')] = true;
        }

        // накатываем новые значения интервалов для текущего рабочего кресла
        foreach ($this->schedulesTable[$workChairId] as $intervalTime => &$interval)
        {
            if(!$exceptFromUpdate[$intervalTime])
            {
                $interval = array_merge($interval, $fields);
            }
        }

        $this->cleanIntervals();

        $this->save();
    }

    /**
     * Рабивает интервалы, которые попадают на начало и конец указанного временного диапазона
     * @param \DateTime $startDateTime - начало временного диапазона
     * @param \DateTime $endDateTime - конец временного диапазона
     * @param $workChairId - id рабочего кресла
     */
    protected function splitIntervals(\DateTime $startDateTime, \DateTime $endDateTime, $workChairId)
    {
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
     * @throws \Exception
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
    protected static function isHalfTime($time)
    {
        $standardIntervalMinutes = ScheduleTable::STANDARD_INTERVAL / 60;
        return (int)$time->format('i') % $standardIntervalMinutes == $standardIntervalMinutes / 2;
    }
}