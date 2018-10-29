<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 30.05.2018
 * Time: 16:19
 */
namespace Mmit\NewSmile;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Orm\ExtendedFieldsDescriptor;
use Mmit\NewSmile\Orm\Helper;
use Mmit\NewSmile;

Loc::loadMessages(__FILE__);

class WaitingListTable extends Entity\DataManager implements ExtendedFieldsDescriptor
{
    /**
     * Период проверки свободных приемов для записей листа ожидания
     */
    const FREE_INTERVALS_CHECK_PERIOD = 1200;
    /**
     * Модификатор даты, который будет использовать агент для получения минимального времени приемов,
     * которые можно предлагать ожидающим. Например, при значении tomorrow будут предлагаться приемы не раньше, чем через сутки.
     */
    const SCHEDULE_START_TIME_MODIFIER = 'tomorrow';
    const AGENT_NAME = self::class . '::checkFreeIntervalsAgent();';


    public static function getTableName()
    {
        return 'm_newsmile_waitinglist';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID',
            )),
            new Entity\DatetimeField('TIMESTAMP_X', array(
                'title' => 'Дата добавления',
                'default_value' => DateTime::createFromTimestamp(time())
            )),
            new Entity\IntegerField('PATIENT_ID', array(
                'required' => true,
                'title' => 'Пациент'
            )),
            new Entity\ReferenceField('PATIENT',
                'Mmit\NewSmile\PatientCard',
                array('=this.PATIENT_ID' => 'ref.ID'),
                array(
                    'title' => 'Пациент'
                )
            ),
            new Entity\IntegerField('DOCTOR_ID', array(
                'title' => 'Врач'
            )),
            new Entity\ReferenceField('DOCTOR',
                'Mmit\NewSmile\Doctor',
                array('=this.DOCTOR_ID' => 'ref.ID'),
                array(
                    'title' => 'Врач'
                )
            ),
            new Entity\IntegerField('CLINIC_ID', array(
                'title' => 'Клиника'
            )),
            new Entity\ReferenceField('CLINIC',
                'Mmit\NewSmile\Clinic',
                array('=this.CLINIC_ID' => 'ref.ID'),
                array(
                    'title' => 'Клиника'
                )
            ),
            new Entity\IntegerField('DURATION', array(
                'title' => 'Длительность',
                'default_value' => 30
            )),
            new Entity\TextField('DATE', array(
                'title' => 'Дни',
                'serialized' => true
            )),
            new Entity\DatetimeField('TIME_START', array(
                'title' => 'Начальное время'
            )),
            new Entity\DatetimeField('TIME_END', array(
                'title' => 'Конечное время'
            )),
            new Entity\EnumField('SPECIALIZATION', array(
                'title' => 'Профессия',
                'values' => static::getSpecializationsEnumValues(),
                'default_value' => ''
            )),
            new Entity\ReferenceField('DOCTOR',
                DoctorTable::class,
                array('=this.DOCTOR_ID' => 'ref.ID'),
                array(
                    'title' => 'Врач'
                )
            ),
            new Entity\IntegerField('DOCTOR_ID', array(
                'title' => 'Врач'
            )),
        );
    }

    public static function addAgent()
    {
        \CAgent::AddAgent(
            static::AGENT_NAME,
            'mmit.newsmile',
            'N',
            static::FREE_INTERVALS_CHECK_PERIOD
        );
    }

    public static function deleteAgent()
    {
        \CAgent::RemoveAgent(
            static::AGENT_NAME,
            'mmit.newsmile'
        );
    }

    public static function checkFreeIntervalsAgent()
    {
        static::checkFreeIntervals();
        return static::AGENT_NAME;
    }

    public static function checkFreeIntervals()
    {
        $suitableIntervals = [];

        $dbWaitingListItems = static::getList();

        if(!$dbWaitingListItems->getSelectedRowsCount()) return;

        $startTime = new \DateTime(static::SCHEDULE_START_TIME_MODIFIER);

        $schedules = [];
        $doctorsIds = [];

        /* запрос свободных для записи интервалов расписания */

        $dbSchedules = ScheduleTable::getList([
            'filter' => [
                '!DOCTOR_ID' => false,
                'PATIENT_ID' => false,
                '>=TIME' => DateTime::createFromPhp($startTime)
            ],
            'order' => [
                'WORK_CHAIR_ID' => 'asc',
                'TIME' => 'asc'
            ]
        ]);

        while($schedule = $dbSchedules->fetch())
        {
            $schedules[] = $schedule;
            $doctorsIds[] = $schedule['DOCTOR_ID'];
        }


        /* запрос профессий врачей */

        $specializations = [];

        $dbSpecs = DoctorSpecializationTable::getList([
            'filter' => [
                'DOCTOR_ID' => $doctorsIds
            ]
        ]);

        while($spec = $dbSpecs->fetch())
        {
            $specializations[$spec['DOCTOR_ID']][$spec['SPECIALIZATION']] = true;
        }

        /* фильтрация записей расписания для каждого из ожидающих пациентов */
        $waitingList = [];

        while($waitingListItem = $dbWaitingListItems->fetch())
        {
            $waitingList[] = $waitingListItem;

            foreach ($schedules as $schedule)
            {
                if(static::isScheduleSuitable($schedule, $waitingListItem, $specializations))
                {
                    $suitableIntervals[$waitingListItem['PATIENT_ID']][$schedule['WORK_CHAIR_ID']][] = $schedule;
                }
            }
        }

        /*
        На этом моменте $suitableIntervals - это отдельные интервалы расписания, которые подходят под фильтры, указанные
        в записи ожидания. То есть под все фильтры, кроме DURATION, тк DURATION в данном случае - это продолжительность
        намечаемого приема, а не продолжительность интервала расписания
        */

        $suitableTime = static::getSuitableTime($suitableIntervals, $waitingList);

        foreach ($suitableTime as $patientId => $patientSuitableTime)
        {
            NewSmile\Notice\NoticeTable::push(
                'WAITING_LIST_SUGGEST',
                [
                    'PATIENT_ID' => $patientId,
                    'FREE_TIME' => $patientSuitableTime
                ],
                ['admin']
            );
        }
    }

    /**
     * Функция собирает из переданных записей расписания интервалы, в которые можно записать пациентов из листа ожидания
     *
     * @param array $suitableIntervals - записи расписания, из которых будут составляться интервалы. Должны быть отсортированы по
     * рабочим креслам и времени (по возрастанию)
     * @param array $waitingList - записи листа ожидания, для которых нужно сформировать интервалы
     *
     * @return array массив подходящего времени для каждого пациента из листа ожидания
     */
    protected static function getSuitableTime(array $suitableIntervals, array $waitingList)
    {
        $suitableTime = [];

        foreach ($waitingList as $waitingListItem)
        {
            $intervals = $suitableIntervals[$waitingListItem['PATIENT_ID']];

            foreach ($intervals as $workChairId => $workChairIntervals)
            {
                /**
                 * @var DateTime $intervalStartTime
                 */
                $intervalStartTime = null;
                $nextIntervalTime = new \DateTime();
                $intervalsCount = count($workChairIntervals);
                $intervalsCounter = 0;

                foreach ($workChairIntervals as $workChairInterval)
                {
                    $intervalsCounter++;
                    $isLast = ($intervalsCounter == $intervalsCount);

                    if($intervalStartTime)
                    {
                        if($isLast || !NewSmile\Date\Helper::isDateTimeEquals($nextIntervalTime, $workChairInterval['TIME']))
                        {
                            $durationMinutes = NewSmile\Date\Helper::getDiffMinutes($intervalStartTime, $nextIntervalTime);
                            if($durationMinutes >= $waitingListItem['DURATION'])
                            {
                                $suitableTime[$waitingListItem['PATIENT_ID']][$intervalStartTime->format('Y-m-d')][] = [
                                    'START_TIME' => $intervalStartTime->format('H:i'),
                                    'END_TIME' => $nextIntervalTime->format('H:i'),
                                    'WORK_CHAIR' => $workChairId
                                ];
                            }

                            $intervalStartTime = $workChairInterval['TIME'];
                        }
                    }
                    else
                    {
                        $intervalStartTime = $workChairInterval['TIME'];
                    }

                    // прибавив DURATION к времени текущей записи расписания мы получаем время следующей записи $nextIntervalTime.
                    // Если на следующей итерации время записи окажется другим, значит интервал завершен
                    $nextIntervalTime->setTimestamp($workChairInterval['TIME']->getTimestamp());
                    $nextIntervalTime->modify('+' . $workChairInterval['DURATION'] . ' minute');
                }
            }
        }

        return $suitableTime;
    }

    protected static function isScheduleSuitable(array $schedule, array $waitingListItem, array $specializations)
    {
        $isSuitable = false;

        $scheduleTimeTs = $schedule['TIME']->getTimestamp();
        $scheduleDate = $schedule['TIME']->format('Y-m-d');

        /* проверка по дате */
        foreach ($waitingListItem['DATE'] as $waitingDate)
        {
            if($waitingDate == $scheduleDate)
            {
                $isSuitable = true;
                break;
            }
        }

        if(!$isSuitable) return false;

        /* проверка по начальному времени */
        if($waitingListItem['TIME_START'] instanceof DateTime)
        {
            $waitingStartTime = NewSmile\Date\Helper::getPhpDateTime($waitingListItem['TIME_START']);
            $waitingStartTime->modify($scheduleDate);

            if($scheduleTimeTs < $waitingStartTime->getTimestamp()) return false;
        }

        /* проверка по конечному времени */
        if($waitingListItem['TIME_END'] instanceof DateTime)
        {
            $waitingEndTime = NewSmile\Date\Helper::getPhpDateTime($waitingListItem['TIME_END']);
            $waitingEndTime->modify($scheduleDate);

            if($scheduleTimeTs >= $waitingEndTime->getTimestamp()) return false;
        }

        /* проверка по врачу */
        if($waitingListItem['DOCTOR_ID'] && ($schedule['DOCTOR_ID'] != $waitingListItem['DOCTOR_ID']))
        {
            return false;
        }

        /* проверка по клинике */
        if($waitingListItem['CLINIC_ID'] && ($schedule['CLINIC_ID'] != $waitingListItem['CLINIC_ID']))
        {
            return false;
        }

        /* проверка по профессии */
        if($waitingListItem['SPECIALIZATION'])
        {
            if(!$specializations[$schedule['DOCTOR_ID']][$waitingListItem['SPECIALIZATION']])
            {
                return false;
            }
        }

        return true;
    }


    public static function getEnumVariants($enumFieldName)
    {
        return DoctorSpecializationTable::getEnumVariants($enumFieldName);
    }

    protected static function getSpecializationsEnumValues()
    {
        return array_keys(static::getEnumVariants('SPECIALIZATION'));
    }
}
