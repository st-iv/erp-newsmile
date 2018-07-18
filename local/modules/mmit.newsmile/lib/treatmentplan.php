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
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

class TreatmentPlanTable extends Entity\DataManager
{

    const CODE_IBLOCK_SERVICES = 'services';

    /**
     * Возвращает id инфоблока с Услугами
     *
     * @return int
     */
    public static function getIDIblockServices()
    {
        if (Loader::includeModule('iblock')) {

            $rsIBlock = \CIBlock::GetList(
                array(),
                array(
                    'CODE' => self::CODE_IBLOCK_SERVICES
                )
            );

            if ($arIBlock = $rsIBlock->Fetch()) {
                return $arIBlock['ID'];
            }
        }
        return 0;
    }

    public static function addItemToTreatmentPlan($ID, $elementID, $arMeasure)
    {
        $arFields = array();
        if (intval($ID)) {
            $arFields['PLAN_ID'] = intval($ID);
        }
        if (intval($elementID)) {
            $arFields['PRODUCT_ID'] = intval($elementID);
        }
        $arFields['QUANTITY'] = 1;
        $arMeasureTemp = [];
        /*
         * сделать проверку на единицу измерения
         */
        if (Loader::includeModule('catalog')) {
            $rsProduct = \Bitrix\Catalog\ProductTable::getList([
                'filter' => [
                    'ID' => $arFields['PRODUCT_ID']
                ]
            ]);
            if ($arProduct = $rsProduct->fetch()) {
                if ($arProduct['MEASURE'] == 7) {
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
                } elseif ($arProduct['MEASURE'] == 6) {
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
        /*
         *
         */
        $arFields['MIN_PRICE'] = 0;
        $arFields['MAX_PRICE'] = 0;
        $arFields['MIN_SUM'] = 0;
        $arFields['MAX_SUM'] = 0;
        if (Loader::includeModule('iblock')) {
            $rsResult = \CIBlockElement::GetList(
                array(),
                array(
                    'ID' => $arFields['PRODUCT_ID']
                ),
                false,
                false,
                array(
                    'ID',
                    'PROPERTY_MINIMUM_PRICE',
                    'PROPERTY_MAXIMUM_PRICE'
                )
            );
            if ($arResult = $rsResult->Fetch()) {
                $arFields['MIN_PRICE'] = $arResult['PROPERTY_MINIMUM_PRICE_VALUE'];
                $arFields['MAX_PRICE'] = $arResult['PROPERTY_MAXIMUM_PRICE_VALUE'];
                $arFields['MIN_SUM'] = $arFields['MIN_PRICE'] * $arFields['QUANTITY'];
                $arFields['MAX_SUM'] = $arFields['MAX_PRICE'] * $arFields['QUANTITY'];
            }
        }
        foreach ($arMeasureTemp as $measure)
        {
            $arFields['MEASURE'] = $measure;
            TreatmentPlanItemTable::add($arFields);
        }
    }

    public static function getTableName()
    {
        return 'm_newsmile_treatmentplan';
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
                'default_value' => 0
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
                'default_value' => 0
            )),
            new Entity\ReferenceField('USER_UPDATE',
                'Bitrix\Main\User',
                array('=this.USER_UPDATE_ID' => 'ref.ID'),
                array(
                    'title' => 'Кто изменил'
                )
            ),
            new Entity\IntegerField('PATIENT_ID', array(
                'title' => 'PATIENT_ID',
            )),
            new Entity\ReferenceField('PATIENT',
                'Mmit\NewSmile\PatientCard',
                array('=this.PATIENT_ID' => 'ref.ID'),
                array(
                    'title' => 'Карточка пациента'
                )
            ),
            new Entity\StringField('NAME', array(
                'primary' => true,
                'title' => 'Название'
            )),
            new Entity\DateField('DATE_START', array(
                'title' => 'Действителен с',
                'default_value' => DateTime::createFromTimestamp(time())
            )),
        );
    }
}
