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
use Bitrix\Seo\Engine\Bitrix;

Loc::loadMessages(__FILE__);

class TreatmentPlanItemTable extends Entity\DataManager
{

    public static function getTableName()
    {
        return 'm_newsmile_treatmentplan_item';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID',
            )),
            new Entity\IntegerField('PLAN_ID', array(
                'title' => 'PLAN_ID',
            )),
            new Entity\ReferenceField('PLAN',
                'Mmit\NewSmile\TreatmentPlan',
                array('=this.PLAN_ID' => 'ref.ID'),
                array(
                    'title' => 'План лечения'
                )
            ),
            new Entity\IntegerField('SERVICE_ID', array(
                'title' => 'SERVICE_ID',
            )),
            new Entity\ReferenceField('SERVICE',
                Service\ServiceTable::class,
                array('=this.SERVICE_ID' => 'ref.ID'),
                array(
                    'title' => 'Услуга'
                )
            ),
            new Entity\IntegerField('QUANTITY', array(
                'title' => 'Количество',
            )),
            new Entity\StringField('TARGET', array(
                'title' => 'Единица'
            )),
            new Entity\FloatField('MIN_PRICE', array(
                'title' => 'Минимальная цена'
            )),
            new Entity\FloatField('MAX_PRICE', array(
                'title' => 'Максимальная цена'
            )),
        );
    }
}
