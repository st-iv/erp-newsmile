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

class PatientCardTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_patientcard';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => Loc::getMessage('MMIT_VISIT_ID'),
            )),
            new Entity\DatetimeField('TIMESTAMP_X', array(
                'title' => Loc::getMessage('MMIT_VISIT_TIMESTAMP_X'),
                'default_value' => DateTime::createFromTimestamp(time())
            )),
            new Entity\StringField('NAME', array(
                'required' => true,
                'title' => Loc::getMessage('MMIT_VISIT_NAME'),
                'default_value' => function () {
                    return Loc::getMessage('MMIT_VISIT_NAME_DEFAULT_VALUE');
                },
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new Entity\ReferenceField('USER',
                'Bitrix\Main\User',
                array('=this.USER_ID' => 'ref.ID'),
                array(
                    'title' => 'Пользователь'
                )
            )

        );
    }
}
