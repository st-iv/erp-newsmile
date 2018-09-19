<?
namespace Mmit\NewSmile;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\Type\DateTime;

class PackingListTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_packing_list';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID',
            )),
            new Entity\DatetimeField('DATE', array(
                'title' => 'Дата накладной',
                'default_value' => DateTime::createFromTimestamp(time())
            )),
            new Entity\DatetimeField('RECEIVING_DATE', array(
                'title' => 'Дата получения',
                'default_value' => DateTime::createFromTimestamp(time())
            )),
            new Entity\ReferenceField('STORE',
                'Mmit\NewSmile\StoreTable',
                array('=this.STORE_ID' => 'ref.ID'),
                array(
                    'title' => 'Склад',
                    'required' => true,
                )
            ),
            new Entity\IntegerField('STORE_ID', array(
                'title' => 'Склад',
                'required' => true
            )),
        );
    }

    public static function onUpdate(Event $event)
    {
        $fields = $event->getParameter('fields');
        $id = $event->getParameter('primary');

        $initialFields = static::getById($id)->fetch();

        if($initialFields['STORE_ID'] != $fields['STORE_ID'])
        {
            static::transferMaterials($id, $initialFields['STORE_ID'], $fields['STORE_ID']);
        }
    }

    public static function onAfterDelete(Event $event)
    {
        static::cascadeDelete($event->getParameter('primary'));
    }

    public static function cascadeDelete($id)
    {
        $dbPackingListItems = PackingListItemTable::getList(array(
            'filter' => array(
                'PACKING_LIST_ID' => $id
            ),
            'select' => array('ID')
        ));

        while($item = $dbPackingListItems->fetch())
        {
            PackingListItemTable::delete($item['ID']);
        }
    }

    public static function transferMaterials($packingListId, $sourceStoreId, $receiverStoreId)
    {
        $dbItems = PackingListItemTable::getList(array(
            'filter' => array(
                'PACKING_LIST_ID' => $packingListId
            ),
            'select' => array('MATERIAL_ID', 'QUANTITY')
        ));

        $materialQuantity = array();

        while($item = $dbItems->fetch())
        {
            $materialQuantity[$item['MATERIAL_ID']] = $item['QUANTITY'];
        }

        if($materialQuantity)
        {
            MaterialQuantityTable::transferMaterials($sourceStoreId, $receiverStoreId, $materialQuantity);
        }
    }
}