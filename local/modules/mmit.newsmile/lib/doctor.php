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

class DoctorTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_doctor';
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
            new Entity\StringField('NAME', array(
                'required' => true,
                'title' => 'ФИО',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new Entity\StringField('COLOR', array(
                'title' => 'Цвет',
                'default_value' => function () {
                    return '#fff';
                }
            )),
            new Entity\IntegerField('USER_ID', array(
                'title' => 'Пользователь',
                'default_value' => 0
            )),
            new Entity\ReferenceField('USER',
                'Bitrix\Main\User',
                array('=this.USER_ID' => 'ref.ID'),
                array(
                    'title' => 'Пользователь'
                )
            ),
            new Entity\IntegerField('CLINIC_ID', array(
                'title' => 'Клиника',
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
