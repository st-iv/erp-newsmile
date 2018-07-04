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
                'title' => 'ID',
            )),
            new Entity\DatetimeField('TIMESTAMP_X', array(
                'title' => Loc::getMessage('MMIT_VISIT_TIMESTAMP_X'),
                'default_value' => 'Дата создания'
            )),
            new Entity\StringField('NAME', array(
                'required' => true,
                'title' => 'ФИО',
                'default_value' => function () {
                    return Loc::getMessage('MMIT_VISIT_NAME_DEFAULT_VALUE');
                },
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new Entity\IntegerField('USER_ID', array(
                'title' => 'Пользователь',
            )),
            new Entity\ReferenceField('USER',
                'Bitrix\Main\User',
                array('=this.USER_ID' => 'ref.ID'),
                array(
                    'title' => 'Пользователь'
                )
            ),
            new Entity\IntegerField('STATUS_ID',
                array(
                    'title' => 'Статус пациента'
                )
            ),
            new Entity\ReferenceField('STATUS',
                'Mmit\NewSmile\StatusPatient',
                array('=this.STATUS_ID' => 'ref.ID'),
                array(
                    'title' => 'Пользователь'
                )
            ),
            new Entity\StringField('NUMBER',
                array(
                    'title' => 'Номер карты'
                )
            ),
            new Entity\StringField('FIRST_PRICE',
                array(
                    'title' => 'Начальная сумма лечения'
                )
            ),
            new Entity\DatetimeField('FIRST_VISIT',
                array(
                    'title' => 'Первый прием'
                )
            ),
            new Entity\StringField('REPRESENTATIVE',
                array(
                    'title' => 'Представитель'
                )
            ),
            new Entity\StringField('PARENTS',
                array(
                    'title' => 'Родитель'
                )
            ),
            new Entity\BooleanField('SMS_NOTICE',
                array(
                    'title' => 'СМС рассылка'
                )
            ),
            new Entity\TextField('COMMENT',
                array(
                    'title' => 'Комментарий'
                )
            ),
            new Entity\IntegerField('DOCTORS_ID',
                array(
                    'title' => 'Лечащие врачи',
                    'serialized' => true,
                    'save_data_modification' => function(){
                        return [
                            function($value){
                                if(!is_array($value)){
                                    return [$value];
                                }else{
                                    return $value;
                                }
                            }
                        ];
                    },
                )
            ),
            new Entity\ReferenceField('DOCTORS',
                'Mmit\NewSmile\Doctor',
                array('=this.DOCTORS_ID' => 'ref.ID'),
                array(
                    'title' => 'Лечащие врачи'
                )
            ),
            new Entity\BooleanField('NEED_CHECK',
                array(
                    'title' => 'Нужен чек'
                )
            ),
            new Entity\StringField('PASSPORT_SN',
                array(
                    'title' => 'Серия и номер'
                )
            ),
            new Entity\StringField('PASSPORT_ISSUED_BY',
                array(
                    'title' => 'Кем выдан'
                )
            ),
            new Entity\DateField('PASSPORT_ISSUED_DATE',
                array(
                    'title' => 'Дата выдачи'
                )
            ),
            new Entity\StringField('PASSPORT_ADDRESS',
                array(
                    'title' => 'Адрес регистрации'
                )
            ),
            new Entity\DateField('PASSPORT_ADDRESS_DATE',
                array(
                    'title' => 'Дата регистрации'
                )
            ),
            new Entity\TextField('PASSPORT_OTHER',
                array(
                    'title' => 'Другой документ'
                )
            ),
            new Entity\StringField('SOURCE',
                array(
                    'title' => 'Источник'
                )
            ),
            new Entity\EnumField('ARCHIVE',
                array(
                    'title' => 'Архив',
                    'values' => array('Нет', 'Недовольство качеством', 'Недовольство ценой', 'Переезд', 'Причина не известна'),
                    'default_value' => 0
                )
            ),
            new Entity\IntegerField('FAMILY_ID',
                array(
                    'title' => 'Члены семьи',
                    'serialized' => true,
                    'save_data_modification' => function(){
                        return [
                            function($value){
                                if(!is_array($value)){
                                    return [$value];
                                }else{
                                    return $value;
                                }
                            }
                        ];
                    },
                )
            ),
            new Entity\ReferenceField('FAMILY',
                'Mmit\NewSmile\PatientCard',
                array('=this.FAMILY_ID' => 'ref.ID'),
                array(
                    'title' => 'Лечащие врачи'
                )
            ),
            new Entity\EnumField('JOINT_ACCOUNT', array(
                    'title' => 'Общий счет'
                )
            )

        );
    }
}
