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

class FileTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_file';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID',
            )),
            new Entity\DatetimeField('DATE_CREATE', array(
                'title' => 'Дата создания',
                'default_value' => DateTime::createFromTimestamp(time())
            )),
            new Entity\StringField('NAME', array(
                'required' => true,
                'title' => 'Название',
                'default_value' => function () {
                    return 'Без названия';
                },
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new Entity\IntegerField('FILE_ID', array(
                'title' => 'Файл',
                'default_value' => 0,
            )),
            new Entity\EnumField('TYPE', array(
                'title' => 'Тип',
                'default_value' => 0,
                'values' => [
                    'Другое',
                    'Фото',
                    'Снимок',
                    'Документ',
                    'Другое',
                ]
            )),
            new Entity\IntegerField('PATIENT_ID', array(
                'title' => 'Карточка пациента',
            ))

        );
    }
    /*Удаление файла вместе с удалением элемента*/
    public static function onBeforeDelete(Entity\Event $event)
    {
        $primary = $event->getParameter("primary");
        $rs = static::GetByID($primary["ID"]);
        if($ar = $rs->Fetch()){
            if (intval($ar['FILE'])>0)
            {
                CFile::Delete($ar['FILE']);
            }
        }
    }

    /*Удаление старого файла при обновлении элемента*/
    public static function onBeforeUpdate(Entity\Event $event)
    {
        $fields = $event->getParameter("fields");
        $primary = $event->getParameter("primary");
        if(intval($fields['FILE'])>0){
            $rs = static::GetByID($primary["ID"]);
            if($old = $rs->Fetch()){
                if (intval($old['FILE'])>0 && $fields["FILE"]!=$old["FILE"])
                {
                    CFile::Delete($old['FILE']);
                }
            }
        }
    }
}
