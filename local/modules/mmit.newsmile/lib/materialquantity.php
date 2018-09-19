<?
namespace Mmit\NewSmile;

use Bitrix\Main\Entity;

class MaterialQuantityTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_material_quantity';
    }

    public static function getMap()
    {
        return array(
            new Entity\ReferenceField('STORE',
                'Mmit\NewSmile\StoreTable',
                array('=this.STORE_ID' => 'ref.ID'),
                array(
                    'primary' => true
                )
            ),
            new Entity\IntegerField('STORE_ID', array(
                'primary' => true
            )),
            new Entity\ReferenceField('MATERIAL',
                'Mmit\NewSmile\MaterialQuantityTable',
                array('=this.MATERIAL_ID' => 'ref.ID'),
                array()
            ),
            new Entity\IntegerField('MATERIAL_ID', array(
                'primary' => true
            )),
            new Entity\IntegerField('QUANTITY', array(
                'required' => true
            )),
        );
    }

    /**
     * Перемещает материалы между складами
     * @param int $sourceStoreId - id склада-отправителя
     * @param int $receiverStoreId - id склада-получателя
     * @param array $materialsQuantity - перемещаемое количество материалов в формате MATERIAL_ID => QUANTITY
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function transferMaterials($sourceStoreId, $receiverStoreId, array $materialsQuantity)
    {
        if(!$materialsQuantity || ($sourceStoreId == $receiverStoreId)) return;

        // запрашиваем количество указанных материалов на указанных складах
        $dbQuantityInfo = static::getList(array(
            'filter' => array(
                'STORE_ID' => array($sourceStoreId, $receiverStoreId),
                'MATERIAL_ID' => array_keys($materialsQuantity)
            )
        ));

        $generalQuantityInfo = array();

        while($quantityInfo = $dbQuantityInfo->fetch())
        {
            $generalQuantityInfo[$quantityInfo['MATERIAL_ID']][$quantityInfo['STORE_ID']] = $quantityInfo['QUANTITY'];
        }


        // уменьшаем количество на складе-отправителе и увеличиваем количество на складе-получателе
        foreach ($materialsQuantity as $materialId => $quantityDiff)
        {
            $stores = array($sourceStoreId, $receiverStoreId);

            foreach ($stores as $storeId)
            {
                $actualQuantity = $generalQuantityInfo[$materialId][$storeId];
                $operationSign = (($storeId == $sourceStoreId) ? -1 : 1);
                $newQuantity = $actualQuantity + $quantityDiff * $operationSign;
                $primary = array(
                    'MATERIAL_ID' => $materialId,
                    'STORE_ID' => $storeId
                );

                if(isset($actualQuantity))
                {
                    static::update($primary, array(
                        'QUANTITY' => $newQuantity
                    ));
                }
                else
                {
                    $fields = $primary;
                    $fields['QUANTITY'] = $newQuantity;
                    static::add($fields);
                }
            }
        }
    }


    public static function updateQuantity($quantityDiff, $materialId, $storeId)
    {
        $primary = array(
            'MATERIAL_ID' => $materialId,
            'STORE_ID' => $storeId,
        );

        $materialQuantityRow = static::getByPrimary($primary)->fetch();

        if($materialQuantityRow)
        {
            $fields = array(
                'QUANTITY' => $materialQuantityRow['QUANTITY'] + $quantityDiff
            );

            static::update($primary, $fields);
        }
        else
        {
            $fields = $primary;
            $fields['QUANTITY'] = (($quantityDiff > 0) ? $quantityDiff : 0);
            static::add($fields);
        }
    }
}