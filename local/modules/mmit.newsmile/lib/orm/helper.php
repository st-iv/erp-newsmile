<?
namespace Mmit\NewSmile\Orm;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Config;
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

    /**
     * Добавляет запись в поисковый индекс в формате, необходимом для возможности раздельного поиска по некоторым полям. Этот
     * формат поддерживает компонент newsmile:search.title
     *
     * @param int $id - id записи
     * @param string $category - код категории, как правило, отдельный для каждой сущности. Этот код категории будет использоваться
     * в компоненте newsmile:search.title
     * @param array $mainFields - основной набор полей, для которых будет создана общая запись в поисковом индексе
     * @param array $additionalFields - дополнительные поля, для которых будут созданы отдельные записи в поисковом индексе.
     * По ним можно будет группировать результаты поиска
     *
     * @throws LoaderException
     */
    public static function indexSearch($id, $category, array $mainFields, array $additionalFields = array())
    {
        Loader::includeModule('search');

        $mainSearchTitle = implode(' ', $mainFields);

        \CSearch::Index(
            "mmit.newsmile",
            $category . '_' . $id,
            [
                "DATE_CHANGE" => date('d.m.Y'),
                "TITLE" => $mainSearchTitle,
                "SITE_ID" => Config::getSiteId(),
                "PARAM1" => $category,
                "PARAM2" => $id,
                "URL" => '',
                "BODY" => '',
            ],
            true
        );

        foreach ($additionalFields as $fieldName => $fieldValue)
        {
            $fieldName = strtolower($fieldName);

            \CSearch::Index(
                "mmit.newsmile",
                $category . '_' . $fieldName . '_' . $id,
                [
                    "DATE_CHANGE" => date('d.m.Y'),
                    "TITLE" => $fieldValue,
                    "SITE_ID" => Config::getSiteId(),
                    "PARAM1" => $category,
                    "PARAM2" => $id,
                    "URL" => '',
                    "BODY" => '',
                ],
                true
            );
        }
    }

    /**
     * Удаляет поисковый индекс элемента с указанным id в указанной категории
     * @param string $id
     * @param string $category
     *
     * @throws \Bitrix\Main\LoaderException
     */
    public static function deleteSearchIndex($id, $category)
    {
        Loader::includeModule('search');

        \CSearch::DeleteIndex(
            'mmit.newsmile',
            false,
            $category,
            $id
        );
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
}