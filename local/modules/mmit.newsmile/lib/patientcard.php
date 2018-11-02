<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 30.05.2018
 * Time: 16:19
 */
namespace Mmit\NewSmile;

use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile;

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
                'required' => true,
                'title' => 'Отчество',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new Entity\StringField('PERSONAL_PHONE', array(
                'title' => 'Телефон',
                'default_value' => '',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new Entity\StringField('PERSONAL_MOBILE', array(
                'title' => 'Дополнительный телефон',
                'default_value' => '',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new Entity\StringField('EMAIL', array(
                'required' => true,
                'title' => 'Email',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
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
            new Entity\DateField('PERSONAL_BIRTHDAY',
                array(
                    'title' => 'Дата рождения',
                    'default_value' => Date::createFromTimestamp(0)
                )
            ),
            new Entity\EnumField('PERSONAL_GENDER',
                array(
                    'title' => 'Пол',
                    'default_value' => 'Мужской',
                    'values' => array('Мужской', 'Женский')
                )
            ),
            new Entity\StringField('PERSONAL_CITY', array(
                    'title' => 'Город',
                    'default_value' => '',
                )
            ),
            new Entity\StringField('PERSONAL_ZIP', array(
                    'title' => 'Почтовый индекс',
                    'default_value' => '',
                )
            ),
            new Entity\TextField('PERSONAL_STREET', array(
                    'title' => 'Улица дом',
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
            new Entity\IntegerField('DOCTORS_ID',
                array(
                    'title' => 'Лечащие врачи',
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
            new Entity\ReferenceField('DOCTORS',
                'Mmit\NewSmile\Doctor',
                array('=this.DOCTORS_ID' => 'ref.ID'),
                array(
                    'title' => 'Лечащие врачи'
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
            new Entity\StringField('SOURCE',
                array(
                    'title' => 'Источник',
                    'default_value' => ''
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
            )

        );
    }

    public static function onAfterAdd(Event $event)
    {
        $primary = $event->getParameter('primary');
        $fields = $event->getParameter('fields');

        static::indexSearch($primary['ID'], $fields);
    }

    public static function onAfterUpdate(Event $event)
    {
        $primary = $event->getParameter('primary');
        $fields = $event->getParameter('fields');

        static::indexSearch($primary['ID'], $fields);
    }

    public static function onAfterDelete(Event $event)
    {
        $primary = $event->getParameter('primary');
        static::deleteSearchIndex($primary['ID']);
    }

    private static function indexSearch($id, $fields)
    {
        $additionalFields = array();

        if(isset($fields['PERSONAL_PHONE']))
        {
            $additionalFields['PERSONAL_PHONE'] = $fields['PERSONAL_PHONE'];
        }

        if(isset($fields['NUMBER']))
        {
            $additionalFields['NUMBER'] = $fields['NUMBER'];
        }

        NewSmile\Orm\Helper::indexSearch(
            $id,
            'patientcard',
            array(Helpers::getFio($fields)),
            $additionalFields
        );
    }

    protected static function deleteSearchIndex($id)
    {
        NewSmile\Orm\Helper::deleteSearchIndex($id, 'patientcard');
    }

    public static function indexSearchAll()
    {
        $rsResult = static::getList();
        while ($arResult = $rsResult->fetch())
        {
            static::indexSearch($arResult['ID'], $arResult);
        }
    }

    /**
     * Метод вовращает массив с информацией о карте клиента, включая информацию пользователя
     *
     * @param $id
     */
    public static function getArrayById($id)
    {
        $rsResult = self::getList(array(
            'select' => array(
                '*',
                'USER_LAST_NAME' => 'USER.LAST_NAME',
                'USER_NAME' => 'USER.NAME',
                'USER_SECOND_NAME' => 'USER.SECOND_NAME',
                'USER_PERSONAL_BIRTHDAY' => 'USER.PERSONAL_BIRTHDAY',
                'USER_PERSONAL_GENDER' => 'USER.PERSONAL_GENDER',
                'USER_PERSONAL_PHONE' => 'USER.PERSONAL_PHONE',
                'USER_PERSONAL_MOBILE' => 'USER.PERSONAL_MOBILE',
                'USER_EMAIL' => 'USER.EMAIL',
                'USER_PERSONAL_CITY' => 'USER.PERSONAL_CITY',
                'USER_PERSONAL_ZIP' => 'USER.PERSONAL_ZIP',
                'USER_PERSONAL_STREET' => 'USER.PERSONAL_STREET',
                'USER_PERSONAL_NOTES' => 'USER.PERSONAL_NOTES',
                'USER_WORK_COMPANY' => 'USER.WORK_COMPANY',
                'USER_WORK_POSITION' => 'USER.WORK_POSITION',
            ),
            'filter' => array(
                'ID' => $id
            )
        ));
        if ($arResult = $rsResult->fetch()) {
            return $arResult;
        }
        return false;
    }

    /**
     * Метод возвращает ID пользователя привязаного к карточке поциента
     *
     * @param $id
     * @return int
     */
    public static function getUserIDByID($id)
    {
        $rsResult = self::getList(array(
            'select' => array(
                'USER_ID'
            ),
            'filter' => array(
                'ID' => $id
            )
        ));
        if ($arResult = $rsResult->fetch()) {
            return $arResult['USER_ID'];
        }
        return 0;
    }
}
