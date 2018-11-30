<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 30.05.2018
 * Time: 16:19
 */
namespace Mmit\NewSmile\Status;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;
use Mmit\NewSmile\Orm\ExtendedFieldsDescriptor;
use Mmit\NewSmile\Status;

Loc::loadMessages(__FILE__);

class ToothTable extends Status implements ExtendedFieldsDescriptor
{
    protected static $enumVariants = [
        'GROUP' => [
            'HEALTHY' => 'здоровый',
            'SICK' => 'больной',
            'CURED' => 'вылеченный',
            'MISSING' => 'отсутствует',
            'NOT_DEFINED' => 'не определен',
        ]
    ];

    public static function getMap()
    {
        $map = parent::getMap();
        $groups = static::getEnumVariants('GROUP');

        $map[] = new Entity\EnumField('GROUP',[
            'values' => array_keys($groups),
            'required' => true
        ]);

        return $map;
    }

    public static function getTableName()
    {
        return 'm_newsmile_status_tooth';
    }

    public static function getEnumVariants($enumFieldName)
    {
        return static::$enumVariants[$enumFieldName];
    }
}
