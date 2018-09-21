<?
namespace Mmit\NewSmile\Orm;

use Bitrix\Main\ORM\Entity;

class Helper
{
    /**
     * Удаляет связанные строки в указанных сущностях
     * @param int $itemId - id строки, связанные строки с которой нужно удалить
     * @param array $dependentEntities - перечисление связанных сущностей  в формате <класс сущности> => <название ключа>
     */
    public static function cascadeDelete($itemId, array $dependentEntities)
    {
        foreach ($dependentEntities as $dependentEntityClass => $keyFieldName)
        {
            $dbDependentRows = $dependentEntityClass::getList(array(
                'filter' => array(
                    $keyFieldName => $itemId
                ),
                'select' => array('ID')
            ));

            while($dependentRow = $dbDependentRows->fetch())
            {
                $dependentEntityClass::delete($dependentRow['ID']);
            }
        }
    }

    public static function getFieldType($field)
    {
        $fieldClassName = get_class($field);
        $fieldClassShortName = substr($fieldClassName, strrpos($fieldClassName, '\\') + 1);
        return strtolower(str_replace('Field', '', $fieldClassShortName));
    }

    public static function getReferenceExternalKeyName(\Bitrix\Main\ORM\Fields\Relations\Reference $referenceField)
    {
        $referenceKey = array_pop(array_keys($referenceField->getReference()));
        preg_match('/this\.([A-z0-9_]+)/', $referenceKey, $matches);
        return $matches[1] ?: '';
    }

    public static function isOrmEntityClass($className)
    {
        return (!empty($className) && is_subclass_of($className, '\Bitrix\Main\Entity\DataManager'));
    }
}