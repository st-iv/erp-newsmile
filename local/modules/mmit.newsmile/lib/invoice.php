<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 30.05.2018
 * Time: 16:19
 */
namespace Mmit\NewSmile;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Catalog\PriceTable;

Loc::loadMessages(__FILE__);

class InvoiceTable extends Entity\DataManager
{

    public static function addItemToInvoice($ID, $elementID, $arMeasure)
    {
        $arFields = array();
        if (intval($ID)) {
            $arFields['INVOICE_ID'] = intval($ID);
        }
        if (intval($elementID)) {
            $arFields['PRODUCT_ID'] = intval($elementID);
        }
        $arFields['QUANTITY'] = 1;
        $arMeasureTemp = [];
        $arFields['PRICE'] = 0;
        $arFields['SUM'] = 0;
        if (Loader::includeModule('catalog')) {
            $rsResult = PriceTable::getList([
                'filter' => [
                    'PRODUCT_ID' => $arFields['PRODUCT_ID']
                ]
            ]);
            if ($arResult = $rsResult->Fetch()) {
                $arFields['PRICE'] = $arResult['PRICE'];
                $arFields['SUM'] = $arFields['PRICE'] * $arFields['QUANTITY'];
            }
            $rsProduct = \Bitrix\Catalog\ProductTable::getList([
                'filter' => [
                    'ID' => $arFields['PRODUCT_ID']
                ]
            ]);
            if ($arProduct = $rsProduct->fetch()) {
                if ($arProduct['MEASURE'] == Option::get('mmit.newsmile', 'id_measure_jowl')) {
                    foreach ($arMeasure as $measure)
                    {
                        $measure = intval($measure);
                        if (($measure > 10 && $measure < 29) || ($measure > 50 && $measure < 66)) {
                            if (!in_array('в.ч.', $arMeasureTemp))
                                $arMeasureTemp[] = 'в.ч.';
                        }
                        if (($measure > 30 && $measure < 49) || ($measure > 70 && $measure < 86)) {
                            if (!in_array('н.ч.', $arMeasureTemp))
                                $arMeasureTemp[] = 'н.ч.';
                        }
                    }
                } elseif ($arProduct['MEASURE'] == Option::get('mmit.newsmile', 'id_measure_tooth')) {
                    foreach ($arMeasure as $measure)
                    {
                        $arMeasureTemp[] = $measure;
                    }
                }
            }
            if (empty($arMeasureTemp)){
                return;
            }
        }
        foreach ($arMeasureTemp as $measure)
        {
            $arFields['MEASURE'] = $measure;
            InvoiceItemTable::add($arFields);
        }
    }

    public static function getTableName()
    {
        return 'm_newsmile_invoice';
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
            new Entity\IntegerField('VISIT_ID', array(
                'title' => 'VISIT_ID',
            )),
            new Entity\ReferenceField('VISIT',
                'Mmit\NewSmile\Visit',
                array('=this.VISIT_ID' => 'ref.ID'),
                array(
                    'title' => 'Прием'
                )
            )
        );
    }
}
