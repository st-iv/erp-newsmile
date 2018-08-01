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

Loc::loadMessages(__FILE__);

class ScheduleTable extends Entity\DataManager
{
    const TIME_15_MINUTES = 900;

    public static function getTableName()
    {
        return 'm_newsmile_schedule';
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
            /*delete*/
            new Entity\StringField('WORK',array(
                'title' => 'Рабочее время',
                'size' => 1,
                'default_value' => 'Y'
            )),
            /*end*/
            new Entity\StringField('ENGAGED',array(
                'title' => 'Занято',
                'size' => 1,
                'default_value' => 'N'
            )),

        );
    }

    /**
     * Создает расписание на 2 недели на основании шаблона расписания из таблицы ScheduleTemplateTable
     *
     * @param $dateStart - формат 'Y-m-d'
     */
    public static function addWeekSchedule($dateStart, $clinicID = 1)
    {
        /* проверка начала недели */
        if (date('N', strtotime($dateStart)) != 1) {
            $dateStart = date('Y-m-d', strtotime('monday this week', strtotime($dateStart)));
        }

        $differenceTime = strtotime($dateStart) - strtotime(ScheduleTemplateTable::DEFAULT_START_DATE);
        $arFields = [
            'CLINIC_ID' => $clinicID,
            '>=TIME' => new DateTime(ScheduleTemplateTable::DEFAULT_START_DATE,'Y-m-d'),
            '<=TIME' => new DateTime(date('Y-m-d', strtotime('monday next week', strtotime(ScheduleTemplateTable::DEFAULT_START_DATE))),'Y-m-d'),
        ];
        /* проверка четной недели */
        if (date('W', strtotime($dateStart)) % 2 == 0) {
            $differenceTime = strtotime($dateStart) - strtotime(ScheduleTemplateTable::DEFAULT_START_DATE_EVEN);
            $arFields = [
                'CLINIC_ID' => $clinicID,
                '>=TIME' => new DateTime(ScheduleTemplateTable::DEFAULT_START_DATE_EVEN,'Y-m-d'),
                '<=TIME' => new DateTime(date('Y-m-d', strtotime('monday next week', strtotime(ScheduleTemplateTable::DEFAULT_START_DATE_EVEN))),'Y-m-d'),
            ];
        }

        $rsSchedule = self::getList([
            'filter' => [
                'CLINIC_ID' => $clinicID,
                '>=TIME' => new DateTime($dateStart,'Y-m-d'),
                '<=TIME' => new DateTime(date('Y-m-d', strtotime('monday next week', strtotime($dateStart))),'Y-m-d'),
            ]
        ]);
        if ($arSchedule = $rsSchedule->fetch()) {
            return false;
        }

        $rsScheduleTemplate = ScheduleTemplateTable::getList(array(
            'filter' => $arFields
        ));
        while ($arScheduleTemplate = $rsScheduleTemplate->fetch())
        {
            $mtTime = $arScheduleTemplate['TIME']->getTimestamp() + $differenceTime;
            $time = date('Y-m-d H:i', $mtTime);

            $arFields = array(
                'TIME' => new DateTime($time, 'Y-m-d H:i'),
                'DOCTOR_ID' => $arScheduleTemplate['DOCTOR_ID'],
                'MAIN_DOCTOR_ID' => $arScheduleTemplate['MAIN_DOCTOR_ID'],
                'WORK_CHAIR_ID' => $arScheduleTemplate['WORK_CHAIR_ID'],
                'CLINIC_ID' => $arScheduleTemplate['CLINIC_ID'],
            );
            self::add($arFields);
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
            $timeIndex += self::TIME_15_MINUTES;
        }
        return true;
    }

    public static function agentAddWeekSchedule($dateStart)
    {
        $rsClinic = ClinicTable::getList([]);
        while ($arClinic = $rsClinic->fetch()) {
            self::addWeekSchedule($dateStart, $arClinic['ID']);
        }
        return __CLASS__ . '::agentAddWeekSchedule();';
    }
}
