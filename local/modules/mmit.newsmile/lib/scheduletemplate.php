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
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class ScheduleTemplateTable extends Entity\DataManager
{
    const DEFAULT_START_DATE = '1993-04-26';
    const DEFAULT_START_DATE_EVEN = '1993-05-03';

    public static function getTableName()
    {
        return 'm_newsmile_schedule_template';
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
                'title' => 'Дата создания',
                'default_value' => DateTime::createFromTimestamp(time())
            )),
            new Entity\DatetimeField('TIME', array(
                'title' => 'Время начала'
            )),
            new Entity\IntegerField('DOCTOR_ID', array(
                'title' => 'DOCTOR_ID',
                'default_value' => 0
            )),
            new Entity\ReferenceField('DOCTOR',
                'Mmit\NewSmile\Doctor',
                array('=this.DOCTOR_ID' => 'ref.ID'),
                array(
                    'title' => 'Врач'
                )
            ),
            new Entity\IntegerField('MAIN_DOCTOR_ID', array(
                'title' => 'DOCTOR_ID',
                'default_value' => 0
            )),
            new Entity\ReferenceField('MAIN_DOCTOR',
                'Mmit\NewSmile\Doctor',
                array('=this.MAIN_DOCTOR_ID' => 'ref.ID'),
                array(
                    'title' => 'Врач'
                )
            ),
            new Entity\IntegerField('WORK_CHAIR_ID', array(
                'title' => 'WORK_CHAIR_ID',
            )),
            new Entity\ReferenceField('WORK_CHAIR',
                'Mmit\NewSmile\WorkChair',
                array('=this.WORK_CHAIR_ID' => 'ref.ID'),
                array(
                    'title' => 'Кресло'
                )
            ),
            new Entity\IntegerField('CLINIC_ID', array(
                'title' => 'CLINIC_ID',
                'default_value' => 1
            )),
            new Entity\ReferenceField('CLINIC',
                'Mmit\NewSmile\Clinic',
                array('=this.CLINIC_ID' => 'ref.ID'),
                array(
                    'title' => 'Клиника'
                )
            ),

        );
    }
    public static function addWeekSchedule($clinicID = 1)
    {
        $dateStart = self::DEFAULT_START_DATE;

        $arResult = array();
        $rsWorkChair = WorkChairTable::getList([
            'filter' => [
                'CLINIC_ID' => $clinicID
            ]
        ]);
        while ($arWorkChair = $rsWorkChair->Fetch())
        {
            $arResult['WORK_CHAIR'][$arWorkChair['ID']] = $arWorkChair;
        }
        $date = strtotime($dateStart);
        $arDays = array(
            strtotime('monday this week', $date),
            strtotime('tuesday this week', $date),
            strtotime('wednesday this week', $date),
            strtotime('thursday this week', $date),
            strtotime('friday this week', $date),
            strtotime('saturday this week', $date),
            strtotime('sunday this week', $date),
            strtotime('monday next week', $date),
            strtotime('tuesday next week', $date),
            strtotime('wednesday next week', $date),
            strtotime('thursday next week', $date),
            strtotime('friday next week', $date),
            strtotime('saturday next week', $date),
            strtotime('sunday next week', $date),
        );
        $arTimes = array();
        $arTimeStart = explode(':', Option::get('mmit.newsmile', "start_time_schedule", '00:00'));
        $arTimeEnd = explode(':', Option::get('mmit.newsmile', "end_time_schedule", '00:00'));

        $timeStart = mktime($arTimeStart[0],$arTimeStart[1],0,0,0,0);
        $timeEnd = mktime($arTimeEnd[0],$arTimeEnd[1],0,0,0,0);
        while ($timeStart < $timeEnd)
        {
            $arTimes[] = date('H:i', $timeStart);

            $timeStart += ScheduleTable::TIME_15_MINUTES;
        }
        foreach ($arDays as $day)
        {
            foreach ($arTimes as $time)
            {
                foreach ($arResult['WORK_CHAIR'] as $arWorkChair)
                {
                    self::add(array(
                        'TIME' => new DateTime(date('Y-m-d', $day) . " " . $time, 'Y-m-d H:i'),
                        'WORK_CHAIR_ID' => $arWorkChair['ID'],
                        'CLINIC_ID' => $clinicID
                    ));
                }
            }
        }

    }

    /**
     * Назначает врача на пол рабочего дня в расписании
     *
     * @param int $dateTime - время начала половины рабочего дня
     * @param int $doctorID
     * @param int $workChair
     * @param int $clinicID
     *
     * @return bool
     */
    public static function appointDoctorHalfDay($dateTime, $doctorID, $workChair, $clinicID = 1)
    {
        if (date('H:i', $dateTime) == '09:00') {
            $timeStart = new DateTime(date('d.m.Y H:i', $dateTime));
            $timeEnd = new DateTime(date('d.m.Y 15:00', $dateTime));
        } else {
            $timeStart = new DateTime(date('d.m.Y H:i', $dateTime));
            $timeEnd = new DateTime(date('d.m.Y 21:00', $dateTime));
        }
        $rsSchedule = self::getList(array(
            'filter' => array(
                '>=TIME' => $timeStart,
                '<TIME' => $timeEnd,
                'WORK_CHAIR_ID' => $workChair,
                'CLINIC_ID' => $clinicID,
            ),
            'select' => array('ID', 'TIME')
        ));
        $arResult = array();
        while ($arSchedule = $rsSchedule->fetch()) {
            $arResult[$arSchedule['TIME']->format('H:i')] = $arSchedule['ID'];
        }
        $timeIndex = $dateTime;
        while ($timeIndex < $timeEnd->getTimestamp())
        {
            if (isset($arResult[date('H:i', $timeIndex)])) {
                self::update(
                    $arResult[date('H:i', $timeIndex)],
                    array(
                        'MAIN_DOCTOR_ID' => $doctorID
                    )
                );
            } else {
                self::add(array(
                    'TIME' => new DateTime(date('d.m.Y H:i', $timeIndex)),
                    'MAIN_DOCTOR_ID' => $doctorID,
                    'WORK_CHAIR_ID' => $workChair,
                    'CLINIC_ID' => $clinicID,
                ));
            }
            $timeIndex += ScheduleTable::TIME_15_MINUTES;
        }
        return true;
    }
}
