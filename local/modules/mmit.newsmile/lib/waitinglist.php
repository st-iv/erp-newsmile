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

class WaitingListTable extends Entity\DataManager
{
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
                'required' => true,
                'title' => 'Врач'
            )),
            new Entity\ReferenceField('DOCTOR',
                'Mmit\NewSmile\Doctor',
                array('=this.DOCTOR_ID' => 'ref.ID'),
                array(
                    'title' => 'Пациент'
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
            new Entity\IntegerField('DURATION', array(
                'title' => 'Длительность',
                'default_value' => 30
            )),
            new Entity\TextField('DATE', array(
                'title' => 'Дни'
            )),
//            new Entity\TextField('DESCRIPTION', array(
//                'title' => 'Описание'
//            ))

        );
    }
}
