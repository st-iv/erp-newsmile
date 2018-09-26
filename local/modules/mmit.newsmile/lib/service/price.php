<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 20.09.2018
 * Time: 17:36
 */

namespace Mmit\NewSmile\Service;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Event;

class PriceTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_service_price';
    }

    public static function getMap()
    {
        return array(
            new Entity\ReferenceField('SERVICE',
                'Mmit\NewSmile\Service\ServiceTable',
                array('=this.SERVICE_ID' => 'ref.ID'),
                array(
                    'title' => 'Услуга'
                )
            ),
            new Entity\IntegerField('SERVICE_ID', array(
                'title' => 'Услуга',
                'primary' => true,
            )),
            new Entity\ReferenceField('CLINIC',
                'Mmit\NewSmile\ClinicTable',
                array('=this.CLINIC_ID' => 'ref.ID'),
                array(
                    'title' => 'Клиника'
                )
            ),
            new Entity\IntegerField('CLINIC_ID', array(
                'title' => 'Клиника',
                'primary' => true,
            )),
            new Entity\FloatField('PRICE', array(
                'title' => 'Цена',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Range(0),
                    );
                },
                'required' => true
            )),
            new Entity\FloatField('MIN_PRICE', array(
                'title' => 'Минимальная цена',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Range(0),
                    );
                },
            )),
            new Entity\FloatField('MAX_PRICE', array(
                'title' => 'Максимальная цена',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Range(0),
                    );
                },
            )),
        );
    }

    public static function onAfterAdd(Event $event)
    {
        $primary = $event->getParameter('primary');
        $fields = $event->getParameter('fields');

        $historyRecordFields = $primary;
        $historyRecordFields['PRICE'] = $fields['PRICE'];
        PriceHistoryTable::add($historyRecordFields);
    }

    public static function onUpdate(Event $event)
    {
        $primary = $event->getParameter('primary');
        $newValues = $event->getParameter('fields');
        $actualValues = static::getByPrimary($primary)->fetch();

        if(isset($newValues['PRICE']) && ($newValues['PRICE'] != $actualValues['PRICE']))
        {
            $historyRecordFields = $primary;
            $historyRecordFields['PRICE'] = $newValues['PRICE'];
            PriceHistoryTable::add($historyRecordFields);
        }
    }

}