<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 30.05.2018
 * Time: 16:19
 */
namespace Mmit\NewSmile;

use Bitrix\Main\Entity;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Access\AccessibleEntity;

Loc::loadMessages(__FILE__);

class ScheduleTable extends Entity\DataManager
{
    const STANDARD_INTERVAL = 1800;

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
            new Entity\IntegerField('PATIENT_ID', array(
                'title' => 'PATIENT_ID',
            )),
            new Entity\ReferenceField('PATIENT',
                'Mmit\NewSmile\PatientCard',
                array('=this.PATIENT_ID' => 'ref.ID'),
                array(
                    'title' => 'Карточка пациента'
                )
            ),
            new Entity\EnumField('DURATION', array(
                'title' => 'Продолжительность',
                'values' => ['15', '30'],
                'default_value' => '30'

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

        /*  подготовка фильтра по расписанию  */

        // фильтр шаблона расписания на нечетную неделю
        $differenceTime = strtotime($dateStart) - strtotime(ScheduleTemplateTable::DEFAULT_START_DATE);
        $arFilter = [
            'CLINIC_ID' => $clinicID,
            '>=TIME' => new DateTime(ScheduleTemplateTable::DEFAULT_START_DATE,'Y-m-d'),
            '<=TIME' => new DateTime(date('Y-m-d', strtotime('monday next week', strtotime(ScheduleTemplateTable::DEFAULT_START_DATE))),'Y-m-d'),
        ];

        // фильтр шаблона расписания на четную неделю
        if (date('W', strtotime($dateStart)) % 2 == 0) {
            $differenceTime = strtotime($dateStart) - strtotime(ScheduleTemplateTable::DEFAULT_START_DATE_EVEN);
            $arFilter = [
                'CLINIC_ID' => $clinicID,
                '>=TIME' => new DateTime(ScheduleTemplateTable::DEFAULT_START_DATE_EVEN,'Y-m-d'),
                '<=TIME' => new DateTime(date('Y-m-d', strtotime('monday next week', strtotime(ScheduleTemplateTable::DEFAULT_START_DATE_EVEN))),'Y-m-d'),
            ];
        }

        // фильтр шаблона main doctors
        $arMainDoctorsFilter = [
            'CLINIC_ID' => $clinicID,
            '>=DATE' => Date::createFromTimestamp($arFilter['>=TIME']->getTimestamp()),
            '<=DATE' => Date::createFromTimestamp($arFilter['<=TIME']->getTimestamp())
        ];

        /* проверка наличия расписания на указанную неделю */
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

        /* добавление расписания по шаблону */

        $rsScheduleTemplate = ScheduleTemplateTable::getList(array(
            'filter' => $arFilter
        ));
        while ($arScheduleTemplate = $rsScheduleTemplate->fetch())
        {
            $mtTime = $arScheduleTemplate['TIME']->getTimestamp() + $differenceTime;
            $time = date('Y-m-d H:i', $mtTime);

            $arFields = array(
                'TIME' => new DateTime($time, 'Y-m-d H:i'),
                'DOCTOR_ID' => $arScheduleTemplate['DOCTOR_ID'],
                'WORK_CHAIR_ID' => $arScheduleTemplate['WORK_CHAIR_ID'],
                'CLINIC_ID' => $arScheduleTemplate['CLINIC_ID'],
            );
            self::add($arFields);
        }

        /* добавление основных врачей по шаблону */

        $rsMainDoctorTemplate = MainDoctorTemplateTable::getList(array(
            'filter' => $arMainDoctorsFilter,
        ));

        while($mainDoctorTemplate = $rsMainDoctorTemplate->fetch())
        {
            $dateTs = $mainDoctorTemplate['DATE']->getTimestamp() + $differenceTime;

            $fields = $mainDoctorTemplate;
            $fields['DATE'] = Date::createFromTimestamp($dateTs);
            $fields['SECOND_DAY_HALF'] = $mainDoctorTemplate['SECOND_DAY_HALF'] == true;

            unset($fields['ID']);

            MainDoctorTable::add($fields);
        }
        return true;
    }

    public static function agentAddWeekSchedule($dateStart)
    {
        $rsClinic = ClinicTable::getList([]);
        while ($arClinic = $rsClinic->fetch()) {
            self::addWeekSchedule($dateStart, $arClinic['ID']);
        }
        return __CLASS__ . "::agentAddWeekSchedule('".date('d.m.Y', strtotime('+1 weeks', $dateStart))."');";
    }


    public static function onBeforeAdd(Event $event)
    {
        $result = new Entity\EventResult();
        $fields = $event->getParameter('fields');

        /* приводим DURATION к строковому типу, чтобы не возникала ошибка при попытке указать число */
        if(is_int($fields['DURATION']))
        {
            $result->modifyFields([
                'DURATION' => (string)$fields['DURATION']
            ]);
        }

        return $result;
    }

    public static function onBeforeUpdate(Event $event)
    {
        $result = new Entity\EventResult();
        $fields = $event->getParameter('fields');

        /* приводим DURATION к строковому типу, чтобы не возникала ошибка при попытке указать число */
        if(is_int($fields['DURATION']))
        {
            $result->modifyFields([
                'DURATION' => (string)$fields['DURATION']
            ]);
        }

        return $result;
    }
}
