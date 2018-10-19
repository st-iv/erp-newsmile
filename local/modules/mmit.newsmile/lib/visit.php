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
use Bitrix\Main\Type\Date;
use Mmit\NewSmile\Orm\ExtendedFieldsDescriptor;

Loc::loadMessages(__FILE__);

class VisitTable extends Entity\DataManager implements ExtendedFieldsDescriptor
{
    const STATUS_START = 'NEW';
    const STATUS_END = 'FINISHED';

    protected static $enumVariants = [
        'STATUS' => [
            'NEW' => 'Новый',
            'WAITING' => 'Пациент ожидает',
            'FINISHED' => 'Прием завершен'
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

    public static function getEnumVariants($enumFieldName)
    {
        return static::$enumVariants[$enumFieldName];
    }
}
