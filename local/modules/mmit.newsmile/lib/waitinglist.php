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
        $freeIntervals = [];

        $dbWaitingListItems = static::getList();

        if(!$dbWaitingListItems->getSelectedRowsCount()) return;

        $startTime = new \DateTime(static::SCHEDULE_START_TIME_MODIFIER);

        $schedules = [];
        $doctorsIds = [];

        $dbSchedules = ScheduleTable::getList([
            'filter' => [
                '!DOCTOR_ID' => false,
                'PATIENT_ID' => false,
                '>=TIME' => DateTime::createFromPhp($startTime)
            ]
        ]);

        while($schedule = $dbSchedules->fetch())
        {
            $schedules[] = $schedule;
            $doctorsIds[] = $schedule['DOCTOR_ID'];
        }

        // запрос профессий врачей
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

        while($waitingListItem = $dbWaitingListItems->fetch())
        {
            foreach ($schedules as $schedule)
            {
                $isSuitable = false;

                $scheduleTime = $schedule['TIME']->format('H:i');
                $scheduleDate = $schedule['TIME']->format('Y-m-d');

                foreach ($waitingListItem['DATE'] as $waitingDate)
                {
                    if($waitingDate == $scheduleDate)
                    {
                        $isSuitable = true;
                        break;
                    }
                }

                if(!$isSuitable) continue;

                if($waitingListItem['TIME_START'] instanceof DateTime)
                {
                    $time = new \DateTime($scheduleTime);
                    $scheduleTimeTs = $time->getTimestamp();
                    $startTime = $waitingListItem['TIME_START']->format('H:i');
                    $time->modify($startTime);
                    $startTimeTs = $time->getTimestamp();

                    if($scheduleTimeTs < $startTimeTs) continue;
                }

                if($waitingListItem['DOCTOR_ID'] && ($schedule['DOCTOR_ID'] != $waitingListItem['DOCTOR_ID']))
                {
                    continue;
                }


                if($waitingListItem['CLINIC_ID'] && ($schedule['CLINIC_ID'] != $waitingListItem['CLINIC_ID']))
                {
                    continue;
                }

                if($waitingListItem['SPECIALIZATION'])
                {
                    if(!$specializations[$schedule['DOCTOR_ID']][$waitingListItem['SPECIALIZATION']])
                    {
                        continue;
                    }
                }

                $freeIntervals[] = $schedule;
            }

        }

        pr('$freeIntervals');
        pr($freeIntervals);
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
