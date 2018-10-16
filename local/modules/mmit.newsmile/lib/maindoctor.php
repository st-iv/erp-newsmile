<?php
namespace Mmit\NewSmile;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile;

Loc::loadMessages(__FILE__);

class MainDoctorTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_main_doctor';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID',
            )),
            new Entity\DateField('DATE', array(
                'title' => 'Дата',
                'default_value' => Date::createFromTimestamp(time())
            )),
            new Entity\ReferenceField('WORK_CHAIR',
                WorkChairTable::class,
                array('=this.WORK_CHAIR_ID' => 'ref.ID'),
                array(
                    'title' => 'Кресло'
                )
            ),
            new Entity\IntegerField('WORK_CHAIR_ID', array(
                'title' => 'Кресло',
            )),
            new Entity\ReferenceField('DOCTOR',
                DoctorTable::class,
                array('=this.DOCTOR_ID' => 'ref.ID'),
                array(
                    'title' => 'Врач'
                )
            ),
            new Entity\IntegerField('DOCTOR_ID', array(
                'title' => 'Врач',
            )),
            new Entity\BooleanField('SECOND_DAY_HALF', array(
                'title' => 'Вторая половина дня',
            )),
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
}
