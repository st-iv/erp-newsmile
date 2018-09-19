<?
namespace Mmit\NewSmile;

use Bitrix\Main\Entity;

class PackingListItemTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_packing_list_item';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
            )),
            new Entity\ReferenceField('PACKING_LIST',
                'Mmit\NewSmile\PackingListTable',
                array('=this.PACKING_LIST_ID' => 'ref.ID'),
                array(
                    'required' => true,
                )
            ),
            new Entity\IntegerField('PACKING_LIST_ID', array(
                'required' => true,
            )),
            new Entity\IntegerField('QUANTITY', array(
                'title' => 'Количество',
                'required' => true,
                'validation' => function () {
                    return array(
                        new Entity\Validator\Range(0),
                    );
                },
            )),
            new Entity\IntegerField('PRICE', array(
                'title' => 'Цена',
                'required' => true,
                'validation' => function () {
                    return array(
                        new Entity\Validator\Range(0),
                    );
                },
            )),
            new Entity\ReferenceField('MATERIAL',
                'Mmit\NewSmile\MaterialTable',
                array('=this.MATERIAL_ID' => 'ref.ID'),
                array()
            ),
            new Entity\IntegerField('MATERIAL_ID', array(
                'required' => true,
                'title' => 'Материал'
            )),
        );
    }

    public static function onAdd(Entity\Event $event)
    {
        $fields = $event->getParameter('fields');

        if($fields['QUANTITY'])
        {
            $dbPackingList = PackingListTable::getByPrimary($fields['PACKING_LIST_ID'], array(
                'select' => array('STORE_ID')
            ));

            if($packingList = $dbPackingList->fetch())
            {
                $fields['STORE_ID'] = $packingList['STORE_ID'];

                MaterialQuantityTable::updateQuantity($fields['QUANTITY'], $fields['MATERIAL_ID'], $fields['STORE_ID']);
            }
        }
    }

    public static function onDelete(Entity\Event $event)
    {
        $fields = static::getByPrimary(
            $event->getParameter('primary'),
            array(
                'select' => array(
                    '*',
                    'STORE_ID' => 'PACKING_LIST.STORE_ID'
                )
            )
        )->fetch();

        if($fields['QUANTITY'])
        {
            MaterialQuantityTable::updateQuantity(-$fields['QUANTITY'], $fields['MATERIAL_ID'], $fields['STORE_ID']);
        }
    }

    public static function onUpdate(Entity\Event $event)
    {
        $oldFieldsValues = static::getByPrimary(
            $event->getParameter('primary'),
            array(
                'select' => array(
                    '*',
                    'STORE_ID' => 'PACKING_LIST.STORE_ID'
                )
            )
        )->fetch();

        $newFields = array_merge($oldFieldsValues, $event->getParameter('fields'));

        if($newFields['MATERIAL_ID'] != $oldFieldsValues['MATERIAL_ID'])
        {
            // если был изменен материал для строки в накладной - удаляем количество из прежнего материала
            MaterialQuantityTable::updateQuantity(
                -$oldFieldsValues['QUANTITY'],
                $oldFieldsValues['MATERIAL_ID'],
                $newFields['STORE_ID']
            );

            $quantityDiff = $newFields['QUANTITY'];
        }
        else
        {
            $quantityDiff = $newFields['QUANTITY'] - $oldFieldsValues['QUANTITY'];
        }

        if($quantityDiff)
        {
            MaterialQuantityTable::updateQuantity(
                $quantityDiff,
                $newFields['MATERIAL_ID'],
                $newFields['STORE_ID']
            );
        }
    }
}