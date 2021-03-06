<?
namespace Mmit\NewSmile\Orm;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\EnumField;
use Bitrix\Main\Entity\ScalarField;
use Bitrix\Main\ORM\Entity;
use \Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\CommandVariable;
use Mmit\NewSmile;

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
                'select' => $dependentEntityClass::getEntity()->getPrimaryArray()
            ));

            while($dependentRowPrimary = $dbDependentRows->fetch())
            {
                $dependentEntityClass::delete($dependentRowPrimary);
            }
        }
    }

    /**
     * Возвращает тип поля из класса поля (название класса поля в нижнем регистре без слова Field). Например,
     * для поля Bitrix\Main\Entity\BooleanField выдаст boolean
     * @param $field - объект поля
     *
     * @return string
     */
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

    /**
     * Проверяет, является ли указанный класс data managerом
     * @param string $className
     *
     * @return bool
     */
    public static function isDataManagerClass($className)
    {
        return (!empty($className) && is_subclass_of($className, '\Bitrix\Main\Entity\DataManager'));
    }

    public static function filterResultArray(array $resultArray, array $filter)
    {
        if(!$resultArray || !$filter) return [];

        $filterOps = [];

        foreach ($filter as $filterName => $filterValue)
        {
            $opsReg = '/^([<>]=?)|[=]/';
            $operation = (preg_match($opsReg, $filterName, $matches) ? $matches[0] : '');
            $filterFieldName =  preg_replace($opsReg, '', $filterName);

            $filterOps[$filterFieldName] = array(
                'OPERATION' => $operation,
                'FIELD_VALUE' => $filter
            );
        }

        return array_filter($resultArray, function($resultItem) use ($filterOps)
        {
            $result = true;

            foreach ($resultItem as $fieldName => $fieldValue)
            {
                if(!$filterOps[$fieldName]) continue;

                if($fieldValue instanceof Date)
                {
                    $fieldValue = $fieldValue->getTimestamp();
                }

                switch($filterOps[$fieldName]['OPERATION'])
                {
                    case '>=':
                        $result = $fieldValue >= $filterOps[$fieldName]['FIELD_VALUE'];
                        break;

                    case '>':
                        $result = $fieldValue > $filterOps[$fieldName]['FIELD_VALUE'];
                        break;

                    case '<=':
                        $result = $fieldValue <= $filterOps[$fieldName]['FIELD_VALUE'];
                        break;

                    case '<':
                        $result = $fieldValue < $filterOps[$fieldName]['FIELD_VALUE'];
                        break;

                    default:
                        $result = $fieldValue == $filterOps[$fieldName]['FIELD_VALUE'];
                }

                if(!$result) break;
            }

            return $result;
        });
    }

    public static function isExtendedFieldsDescriptor($className)
    {
        return is_subclass_of($className, 'Mmit\NewSmile\Orm\ExtendedFieldsDescriptor');
    }

    /**
     * Возвращает описание полей сущности
     * @param string $dataManagerClass
     * @param array $fieldsCodes - массив кодов полей, по которым нужно вернуть информацию
     *
     * @return array
     * @throws NewSmile\Error
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getFieldsDescription($dataManagerClass, $fieldsCodes = [])
    {
        if(!static::isDataManagerClass($dataManagerClass))
        {
            throw new NewSmile\Error($dataManagerClass . ' не является Data Manager');
        }

        /**
         * @var DataManager $dataManagerClass
         */

        $result = [];
        $fieldsCodes = array_flip($fieldsCodes);

        foreach ($dataManagerClass::getEntity()->getFields() as $field)
        {
            /**
             * @var Field $field
             */

            $fieldName = $field->getName();
            if(!($field instanceof ScalarField) || ($fieldsCodes && !isset($fieldsCodes[$fieldName]))) continue;

            $fieldNameCamelCase = NewSmile\Helpers::getCamelCase($fieldName, false);

            $fieldInfo = [
                'required' => $field->isRequired(),
                'title' => $field->getTitle(),
                'name' => $fieldNameCamelCase,
                'type' => Helper::getFieldType($field)
            ];

            $defaultValue = $field->getDefaultValue();
            if(isset($defaultValue) && !is_callable($defaultValue))
            {
                $fieldInfo['defaultValue'] = ($defaultValue instanceof Date) ? $defaultValue->format('Y-m-d') : $defaultValue;
            }

            if($field instanceof EnumField || $field instanceof NewSmile\Orm\Fields\MultipleEnumField)
            {
                if(is_subclass_of($dataManagerClass, ExtendedFieldsDescriptor::class))
                {
                    /**
                     * @var ExtendedFieldsDescriptor $dataManagerClass
                     */
                    $variants = $dataManagerClass::getEnumVariants($fieldName);
                }
                else
                {
                    $variants = $field->getValues();
                }

                array_walk($variants, function(&$variantValue, $variantKey)
                {
                    $variantValue = [
                        'code' => $variantKey ?: $variantValue,
                        'title' => $variantValue
                    ];
                });

                $fieldInfo['variants'] = array_values($variants);
            }

            $result[$fieldNameCamelCase] = $fieldInfo;
        }

        return $result;
    }

    public static function getFieldsDescriptionFormat($resultFieldCode, $resultFieldDescription)
    {
        /*return (new CommandVariable\Object($resultFieldCode, $resultFieldDescription, true))->setShape([
            new CommandVariable\Object('<код поля>', '', )
        ]);*/
    }

    /**
     * Возвращает пространство имён команд, работающих с указаной ORM сущностью
     * @param Entity $entity
     *
     * @return string
     */
    public static function getCommandNamespaceByEntity(Entity $entity)
    {
        $shortClassName = NewSmile\Helpers::getShortClassName($entity->getDataClass());
        return NewSmile\Command\Base::getNamespace() . '\\' . preg_replace('/Table$/', '', $shortClassName);
    }
}