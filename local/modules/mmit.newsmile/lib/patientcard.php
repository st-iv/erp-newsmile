<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 30.05.2018
 * Time: 16:19
 */
namespace Mmit\NewSmile;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Mmit\NewSmile;

Loc::loadMessages(__FILE__);

class PatientCardTable extends Entity\DataManager implements NewSmile\Orm\ExtendedFieldsDescriptor, NewSmile\Search\Searchable
{
    protected static $enumFields = [
        'PERSONAL_GENDER' => [
            'MAN' => 'мужской',
            'WOMAN' => 'женский'
        ],
        'SOURCE' => [
            'INTERNET_ADVERTISING' => 'Реклама в интернете',
            'FRIENDS_RECOMMENDATION' => 'Рекомендация друзей',
            'INTUITION' => 'Интуиция привела',
            'RANDOM' => 'Наткнулся случайно'
        ]
    ];

    public static function getTableName()
    {
        return 'm_newsmile_patientcard';
    }

    public static function getMap()
    {
        $genders = array_keys(static::getEnumVariants('PERSONAL_GENDER'));

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
                'title' => 'Имя',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new Entity\StringField('LAST_NAME', array(
                'required' => true,
                'title' => 'Фамилия',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new Entity\StringField('SECOND_NAME', array(
                'required' => false,
                'title' => 'Отчество',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new NewSmile\Orm\Fields\PhoneField('PERSONAL_PHONE', array(
                'title' => 'Телефон',
                'default_value' => '',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new NewSmile\Orm\Fields\PhoneField('ADDITIONAL_PHONE', array(
                'title' => 'Резервный телефон',
            )),
            new Entity\StringField('EMAIL', array(
                'title' => 'Email',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new Entity\IntegerField('USER_ID', array(
                'title' => 'Пользователь'
            )),
            new Entity\ReferenceField('USER',
                'Bitrix\Main\User',
                array('=this.USER_ID' => 'ref.ID'),
                array(
                    'title' => 'Пользователь'
                )
            ),
            new Entity\DateField('PERSONAL_BIRTHDAY',
                array(
                    'title' => 'Дата рождения',
                    'required' => true
                )
            ),
            new Entity\EnumField('PERSONAL_GENDER',
                array(
                    'title' => 'Пол',
                    'values' => $genders
                )
            ),
            new Entity\StringField('PERSONAL_CITY', array(
                    'title' => 'Город',
                )
            ),
            new Entity\TextField('PERSONAL_STREET', array(
                    'title' => 'Улица',
                )
            ),
            new Entity\TextField('PERSONAL_HOME', array(
                    'title' => 'Дом',
                )
            ),
            new Entity\TextField('PERSONAL_HOUSING', array(
                    'title' => 'Корпус',
                )
            ),
            new Entity\TextField('PERSONAL_APARTMENT', array(
                    'title' => 'Квартира',
                )
            ),
            new Entity\StringField('PERSONAL_ZIP', array(
                    'title' => 'Почтовый индекс',
                    'default_value' => '',
                )
            ),
            new Entity\TextField('PERSONAL_NOTES', array(
                    'title' => 'Дополнительные заметки',
                    'default_value' => '',
                )
            ),
            new Entity\StringField('WORK_COMPANY', array(
                    'title' => 'Место работы',
                    'default_value' => '',
                )
            ),
            new Entity\StringField('WORK_POSITION', array(
                    'title' => 'Профессия',
                    'default_value' => '',
                )
            ),
            new Entity\IntegerField('STATUS_ID',
                array(
                    'title' => 'Статус пациента',
                    'default_value' => 0
                )
            ),
            new Entity\ReferenceField('STATUS',
                'Mmit\NewSmile\Status\Patient',
                array('=this.STATUS_ID' => 'ref.ID'),
                array(
                    'title' => 'Пользователь'
                )
            ),
            new Entity\StringField('NUMBER',
                array(
                    'title' => 'Номер карты',
                    'default_value' => ''
                )
            ),
            new Entity\StringField('FIRST_PRICE',
                array(
                    'title' => 'Начальная сумма лечения',
                    'default_value' => ''
                )
            ),
            new Entity\DatetimeField('FIRST_VISIT',
                array(
                    'title' => 'Первый прием',
                    'default_value' => DateTime::createFromTimestamp(0)
                )
            ),
            new Entity\StringField('REPRESENTATIVE',
                array(
                    'title' => 'Представитель',
                    'default_value' => ''
                )
            ),
            new Entity\StringField('PARENTS',
                array(
                    'title' => 'Родитель',
                    'default_value' => ''
                )
            ),
            new Entity\BooleanField('SMS_NOTICE',
                array(
                    'title' => 'СМС рассылка',
                    'default_value' => 0
                )
            ),
            new Entity\TextField('COMMENT',
                array(
                    'title' => 'Комментарий',
                    'default_value' => ''
                )
            ),
            new Entity\StringField('DOCTORS_ID',
                array(
                    'title' => 'Лечащие врачи',
                    'serialized' => true,
                )
            ),
            new Entity\BooleanField('NEED_CHECK',
                array(
                    'title' => 'Нужен чек',
                    'default_value' => 0
                )
            ),
            new Entity\StringField('RESIDENTIAL_ADDRESS',
                array(
                    'title' => 'Адрес проживания',
                    'default_value' => ''
                )
            ),
            new Entity\StringField('PASSPORT_SN',
                array(
                    'title' => 'Серия и номер',
                    'default_value' => ''
                )
            ),
            new Entity\StringField('PASSPORT_ISSUED_BY',
                array(
                    'title' => 'Кем выдан',
                    'default_value' => ''
                )
            ),
            new Entity\DateField('PASSPORT_ISSUED_DATE',
                array(
                    'title' => 'Дата выдачи',
                    'default_value' => DateTime::createFromTimestamp(0)
                )
            ),
            new Entity\StringField('PASSPORT_PLACE_BIRTH',
                array(
                    'title' => 'Место рождения',
                    'default_value' => ''
                )
            ),
            new Entity\StringField('PASSPORT_ADDRESS',
                array(
                    'title' => 'Адрес регистрации',
                    'default_value' => ''
                )
            ),

            new Entity\DateField('PASSPORT_ADDRESS_DATE',
                array(
                    'title' => 'Дата регистрации',
                    'default_value' => DateTime::createFromTimestamp(0)
                )
            ),
            new Entity\TextField('PASSPORT_OTHER',
                array(
                    'title' => 'Другой документ',
                    'default_value' => ''
                )
            ),
            new NewSmile\Orm\Fields\MultipleEnumField('SOURCE',
                array(
                    'title' => 'Откуда узнал',
                    'values' => array_keys(static::getEnumVariants('SOURCE'))
                )
            ),
            new Entity\EnumField('ARCHIVE',
                array(
                    'title' => 'Архив',
                    'values' => array('Нет', 'Недовольство качеством', 'Недовольство ценой', 'Переезд', 'Причина не известна'),
                    'default_value' => 'Нет'
                )
            ),
            new Entity\IntegerField('FAMILY_ID',
                array(
                    'title' => 'Члены семьи',
                    'default_value' => 0,
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
            new Entity\BooleanField('JOINT_ACCOUNT', array(
                    'title' => 'Общий счет',
                    'default_value' => 0
                )
            ),
            new Entity\TextField('TEETH_MAP',[
                'title' => 'Карта зубов',
                'serialized' => true
            ])
        );
    }

    public static function getMainIndex($fields)
    {
        return sprintf('%s %s %s', $fields['LAST_NAME'], $fields['NAME'], $fields['SECOND_NAME']);
    }

    public static function getSearchableFields()
    {
        return ['PERSONAL_PHONE', 'NUMBER'];
    }


    public static function onBeforeAdd(Event $event)
    {
        $fields = $event->getParameter('fields');
        return static::onBeforeSave(null, $fields);
    }

    public static function onBeforeUpdate(Event $event)
    {
        $primary = $event->getParameter('primary');
        $fields = $event->getParameter('fields');
        $currentFields = static::getByPrimary($primary)->fetch();

        return static::onBeforeSave($primary, array_merge($currentFields, $fields), $currentFields);
    }

    public static function onBeforeSave($primary, $newFields, $oldFields = [])
    {
        $result = new Entity\EventResult();

        if($newFields['PERSONAL_PHONE'] != $oldFields['PERSONAL_PHONE'])
        {
            $user = new \CUser();

            if($oldFields)
            {
                $user->Update($newFields['USER_ID'], [
                    'PERSONAL_PHONE' => $newFields['PERSONAL_PHONE'],
                    'LOGIN' => 'patient_' . $newFields['PERSONAL_PHONE']
                ]);
            }
            elseif(!$newFields['USER_ID'])
            {
                $login = 'patient_' . $newFields['PERSONAL_PHONE'];

                $userId = $user->Add([
                    'PERSONAL_PHONE' => $newFields['PERSONAL_PHONE'],
                    'LOGIN' => $login,
                    'EMAIL' => $login . '@mail.ru',
                    'PASSWORD' => md5($login . rand(0, 100000000)),
                    'UF_ROLES' => ['patient']
                ]);

                if($userId)
                {
                    $result->modifyFields([
                        'USER_ID' => $userId
                    ]);
                }
            }
        }

        return $result;
    }

    public static function getEnumVariants($enumFieldName)
    {
        return static::$enumFields[$enumFieldName];
    }

    public static function getUserIdForPatient($patientId)
    {
        $patient = static::getByPrimary($patientId, [
            'select' => ['USER_ID']
        ])->fetch();

        return $patient['USER_ID'];
    }
}
