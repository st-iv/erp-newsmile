<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 30.05.2018
 * Time: 16:19
 */
namespace Mmit\NewSmile\Visit;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\Type\Date;
use Mmit\NewSmile\Config;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Orm\ExtendedFieldsDescriptor;
use Mmit\NewSmile;
use Mmit\NewSmile\ScheduleTable;

Loc::loadMessages(__FILE__);

class VisitTable extends Entity\DataManager implements ExtendedFieldsDescriptor
{
    const STATUS_START = 'NEW';
    const STATUS_END = 'FINISHED';

    protected static $enumVariants = [
        'STATUS' => [
            'NEW' => 'Новый',
            'WAITING' => 'Пациент ожидает',
            'FINISHED' => 'Завершен',
            'CANCELED' => 'Отменен'
        ]
    ];

    public static function getTableName()
    {
        return 'm_newsmile_visit';
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
                'default_value' => Date::createFromTimestamp(time())
            )),
            new Entity\EnumField('STATUS', array(
                'title' => 'Статус',
                'values' => array_keys(static::getEnumVariants('STATUS')),
                'default_value' => 'NEW'
            )),
            new Entity\DateField('DATE_START', array(
                'title' => 'Дата'
            )),
            new Entity\DatetimeField('TIME_START', array(
                'title' => 'Время начала'
            )),
            new Entity\DatetimeField('TIME_END', array(
                'title' => 'Время окончания'
            )),
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
            new Entity\IntegerField('DOCTOR_ID', array(
                'title' => 'DOCTOR_ID',
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
                'default_value' => function()
                {
                    return Config::getClinicId();
                }
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

    public static function onBeforeAdd(Event $event)
    {
        $fields = $event->getParameter('fields');
        $result = new Entity\EventResult();

        /* выясняем ID врача для приема по интервалам расписания, которые приходятся на прием */
        if(!$fields['DOCTOR_ID'])
        {
            $dbSchedules = ScheduleTable::getList([
                'filter' => [
                    '>=TIME' => $fields['TIME_START'],
                    '<TIME' => $fields['TIME_END'],
                    'WORK_CHAIR_ID' => $fields['WORK_CHAIR_ID'],
                    'CLINIC_ID' => $fields['CLINIC_ID'] ?: Config::getClinicId()
                ]
            ]);

            $doctorId = null;

            while ($schedule = $dbSchedules->fetch())
            {
                if(!$schedule['DOCTOR_ID'])
                {
                    throw new Error('На все интервалы расписания, на которые записывается пациент, должен быть назначен врач!', 'VISIT_NOT_APPOINTED_DOCTOR');
                }

                if(!isset($doctorId))
                {
                    $doctorId = $schedule['DOCTOR_ID'];
                }
                elseif($doctorId != $schedule['DOCTOR_ID'])
                {
                    throw new Error('На все интервалы расписания, на которые записывается пациент, должен быть назначен один и тотже врач!', 'VISIT_DIFFERENT_DOCTORS');
                }
            }

            $result->modifyFields([
                'DOCTOR_ID' => $doctorId
            ]);
        }

        /* берем DATE_START (дату приема) с TIME_START и TIME_END */

        if(!NewSmile\Date\Helper::isEqualDates($fields['TIME_START'], $fields['TIME_END']))
        {
            throw new Error('Время начала приема и время окончания приема должны приходитьтся на одну дату', 'VISIT_DIFFERENT_TIMES_DATE');
        }

        $result->modifyFields([
            'DATE_START' => new Date($fields['TIME_START']->format('d.m.Y'))
        ]);

        return $result;
    }

    public static function onAfterAdd(Event $event)
    {
        $fields = $event->getParameter('fields');


        /* записываем id пациента в интервалы расписания */

        $dbSchedules = ScheduleTable::getList([
            'filter' => [
                '>=TIME' => $fields['TIME_START'],
                '<TIME' => $fields['TIME_END'],
                'WORK_CHAIR_ID' => $fields['WORK_CHAIR_ID'],
                'CLINIC_ID' => $fields['CLINIC_ID']
            ],
            'select' => ['ID']
        ]);

        while($schedule = $dbSchedules->fetch())
        {
            ScheduleTable::update($schedule['ID'], [
                'PATIENT_ID' => $fields['PATIENT_ID'],
                'DOCTOR_ID' => $fields['DOCTOR_ID']
            ]);
        }
    }

    public static function getEnumVariants($enumFieldName)
    {
        return static::$enumVariants[$enumFieldName];
    }
}
