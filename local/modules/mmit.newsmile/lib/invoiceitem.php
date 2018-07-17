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

class InvoiceItemTable extends Entity\DataManager
{

    public static function getTableName()
    {
        return 'm_newsmile_invoice_item';
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
            new Entity\IntegerField('USER_CREATE_ID', array(
                'title' => 'Кто создал',
                'default_value' => function () {
                    global $USER;
                    return $USER->GetID();
                }
            )),
            new Entity\ReferenceField('USER_CREATE',
                'Bitrix\Main\User',
                array('=this.USER_CREATE_ID' => 'ref.ID'),
                array(
                    'title' => 'Кто создал'
                )
            ),
            new Entity\DatetimeField('DATE_UPDATE', array(
                'title' => 'Дата изменения',
                'default_value' => DateTime::createFromTimestamp(time())
            )),
            new Entity\IntegerField('USER_UPDATE_ID', array(
                'title' => 'Кто изменил',
                'default_value' => function () {
                    global $USER;
                    return $USER->GetID();
                }
            )),
            new Entity\ReferenceField('USER_UPDATE',
                'Bitrix\Main\User',
                array('=this.USER_UPDATE_ID' => 'ref.ID'),
                array(
                    'title' => 'Кто изменил'
                )
            ),
            new Entity\IntegerField('INVOICE_ID', array(
                'title' => 'INVOICE_ID',
            )),
            new Entity\ReferenceField('INVOICE',
                'Mmit\NewSmile\Invoice',
                array('=this.INVOICE_ID' => 'ref.ID'),
                array(
                    'title' => 'Счет'
                )
            ),
            new Entity\IntegerField('PRODUCT_ID', array(
                'title' => 'PRODUCT_ID',
            )),
            new Entity\ReferenceField('PRODUCT',
                'Bitrix\Iblock',
                array('=this.PRODUCT_ID' => 'ref.ID'),
                array(
                    'title' => 'Товар'
                )
            ),
            new Entity\IntegerField('QUANTITY', array(
                'title' => 'Количество',
            )),
            new Entity\StringField('MEASURE', array(
                'title' => 'Единица'
            )),
            new Entity\FloatField('PRICE', array(
                'title' => 'Цена'
            )),
            new Entity\FloatField('SUM', array(
                'title' => 'Сумма'
            )),
        );
    }
}
