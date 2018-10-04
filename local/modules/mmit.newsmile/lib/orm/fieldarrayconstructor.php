<?

namespace Mmit\NewSmile\Orm;

use Bitrix\Main\DB\Result;
use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\Field;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ScalarField;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields;
use Mmit\NewSmile\Orm\Fields\ReverseReference;


/**
 * Подготавливает информацию о полях ORM сущности в виде массива
 * Class FieldArrayConstructor
 * @package Mmit\NewSmile\Orm
 */
class FieldArrayConstructor extends FieldsProcessor
{
    protected $query = null;
    protected $queryResultMap = array();
    protected $externalKeyNames = array();

    public function __construct(Entity $entity, array $params)
    {
        parent::__construct($params);

        $this->query = new Query($entity);
    }

    /**
     * Формирует массив для указанного поля, вызывыает специализированные методы вида process<FieldClassName>
     * @param Fields\Field $field
     * @param array $fieldParams
     * @return array|bool
     */
    protected function processField(Fields\Field $field)
    {
        $fieldName = $field->getName();

        $isEditable = in_array('*', $this->params['EDITABLE_FIELDS']) || in_array($fieldName, $this->params['EDITABLE_FIELDS']);
        $isSelected = $isEditable || (!$this->params['SELECT_FIELDS'] || in_array($fieldName, $this->params['SELECT_FIELDS']));

        if (!$isSelected) return false;

        $result = array(
            'NAME' => $fieldName,
            'TITLE' => $field->getTitle(),
            'TYPE' => Helper::getFieldType($field),
            'EDITABLE' => $isEditable,
            'INPUT_NAME' => $fieldName
        );

        if ($field instanceof Fields\ScalarField) {
            $result['REQUIRED'] = $field->isRequired();
            $result['DEFAULT'] = $field->getDefaultValue();
        }

        $this->queryValueDeferred($fieldName);

        return $result;
    }


    protected function processEnumField(Fields\EnumField $field)
    {
        $result = array();
        $dataClass = $field->getEntity()->getDataClass();

        if (is_subclass_of($dataClass, 'Mmit\NewSmile\Orm\ExtendedFieldsDescriptor'))
        {
            $variantsNames = $dataClass::getEnumVariants($field->getName());

        }
        else
        {
            $variantsNames = array();

            foreach ($field->getValues() as $value)
            {
                $variantsNames[$value] = $value;
            }
        }

        foreach ($variantsNames as $variantCode => $variantName)
        {
            $result['VARIANTS'][$variantCode] = array(
                'NAME' => $variantName
            );
        }

        return $result;
    }

    protected function processBooleanField(Fields\BooleanField $field)
    {
        $result = array();
        $fieldValues = $field->getValues();
        $result['TRUE_VALUE'] = $fieldValues[1];
        return $result;
    }

    protected function processReference(Fields\Relations\Reference $field)
    {
        $result = array();
        $keyName = Helper::getReferenceExternalKeyName($field);
        $externalKeyField = $field->getEntity()->getField($keyName);

        $result['REFERENCE_ENTITY_CLASS'] = $field->getRefEntity()->getDataClass();
        $result['REFERENCE_KEY_NAME'] = $keyName;
        $result['INPUT_NAME'] = $keyName;
        $result['VARIANTS'] = $this->getReferenceElements($field);
        $result['TITLE'] = $externalKeyField->getTitle();

        if ($externalKeyField instanceof ScalarField) {
            $result['DEFAULT'] = $externalKeyField->getDefaultValue();
            $result['REQUIRED'] = $externalKeyField->isRequired();
            if ($result['REQUIRED'] && !$result['DEFAULT'] && $result['VARIANTS']) {
                $referenceItems = $result['VARIANTS'];
                $result['DEFAULT'] = array_shift($referenceItems)['ID'];
            }
        }

        $this->queryValueDeferred($field->getName(), $keyName);
        $this->externalKeyNames[] = $keyName;

        return $result;
    }

    /**
     * @param ReverseReference $reverse
     * @return array
     */
    protected function processReverseReference(ReverseReference $reverse)
    {
        $result = array(
            'FIELDS' => array()
        );

        $sourceEntity = $reverse->getSourceEntity();
        $keyField = $reverse->getKeyField();
        $keyName = Helper::getReferenceExternalKeyName($keyField);

        /* получаем параметры для fieldArrayConstructor */

        $paramsKey = $sourceEntity->getDataClass() . ':' . $keyField->getName();
        if ($paramsKey[0] == '\\')
        {
            $paramsKey = substr($paramsKey, 1);
        }

        $initialParams = $this->params['REVERSE_REFERENCES'][$paramsKey];
        $initialParams['SELECT_FIELDS'] = $initialParams['SELECT_FIELDS'] ?: array();
        $initialParams['SELECT_FIELDS'] = array_merge($initialParams['EDITABLE_FIELDS'] ?: array(), $initialParams['SELECT_FIELDS']);

        $params = $initialParams;

        $params['QUERY_FILTER'] = array(
            $keyName => $this->params['ENTITY_ID']
        );

        if($params['SELECT_FIELDS'])
        {
            $params['SELECT_FIELDS'] = array_merge($params['SELECT_FIELDS'], $sourceEntity->getPrimaryArray());
        }
        else
        {
            $params['SELECT_FIELDS'] = $sourceEntity->getPrimaryArray();
        }

        /* проходимся конструктором по всем полям привязавшейся сущности и получаем массив описания полей */

        $fieldArrayConstructor = new static($sourceEntity, $params);

        foreach ($sourceEntity->getFields() as $field)
        {
            $fieldArrayConstructor->process($field);
        }

        if($params['SINGLE_MODE'])
        {
            // в режиме SINGLE_MODE работаем только с одной связанной сущностью, в такой ситуации можно воспользоваться
            // методом writeValues для сохранения значений полей в результативный массив
            $fieldArrayConstructor->writeValues();
        }

        $fieldsArray = $fieldArrayConstructor->getResult();

        foreach ($fieldsArray as &$fieldArray)
        {
            $fieldArray['INPUT_NAME'] = $reverse->getName() . '__' . $fieldArray['INPUT_NAME'] . '[]';
        }

        unset($fieldArray);

        foreach ($sourceEntity->getPrimaryArray() as $primaryFieldName)
        {
            if(!in_array($primaryFieldName, $initialParams['SELECT_FIELDS']))
            {
                $fieldsArray[$primaryFieldName]['TYPE'] = 'hidden';
            }
        }

        /* отменяем запрос значения поля, который регистрируется для всех полей по умолчанию в методe processField */
        $this->removeValueQuery($reverse->getName());

        if($params['SINGLE_MODE'])
        {
            $this->result = array_merge($this->result, $fieldsArray);
            $result = false; // не добавляем поле в результативный массив
        }
        else
        {
            /* сохраняем значения полей */

            // метод queryValues запрашивает значения свойств, но не распределяет их по результативному массиву, в отличие
            // от writeValues. Именно для данного типа полей в результате запроса для каждого поля возвращается несколько
            // значений - это специфичная ситуация, с которой не умеет работать метод writeValues
            $queryValuesResult = $fieldArrayConstructor->queryValues();
            $queryResultMap = $fieldArrayConstructor->getQueryResultMap();

            $result['ITEMS'] = array();
            while ($itemValues = $queryValuesResult->fetch())
            {
                $item = array();
                foreach ($queryResultMap as $targetFieldName => $selectFieldName)
                {
                    $item[$targetFieldName] = $itemValues[$selectFieldName];
                }

                $result['ITEMS'][] = $item;
            }

            $result['FIELDS'] = $fieldsArray;
        }

        return $result;
    }

    /**
     * Получает все элементы связанной сущности по указанному полю Reference
     * @param Fields\Relations\Reference $field
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getReferenceElements(Fields\Relations\Reference $field)
    {
        $result = array();

        $entityClass = $field->getRefEntity()->getDataClass();
        $select = $this->getReferenceSelectFields($field);

        if(Helper::isDataManagerClass($entityClass) && $select)
        {
            $dbElements = $entityClass::getList(array(
                'select' => $select
            ));

            while($element = $dbElements->fetch())
            {
                $result[$element['ID']] = $element;
            }
        }

        return $result;
    }

    /**
     * Получает массив полей, которые необходимо выбрать для указанной сущности на основе параметра SELECT_FIELDS
     * @param Fields\Relations\Reference $referenceField
     * @return array
     */
    protected function getReferenceSelectFields(Fields\Relations\Reference $referenceField)
    {
        $selectFields = array();

        if($this->params['SELECT_FIELDS'])
        {
            $referenceFieldName = $referenceField->getName();

            foreach ($this->params['SELECT_FIELDS'] as $showFieldName)
            {
                if(preg_match('/^' . $referenceFieldName . '\.([A-z0-9_]+)$/', $showFieldName, $matches))
                {
                    $selectFields[] = $matches[1];
                }
            }
        }

        $selectFields[] = 'ID';

        return $selectFields;
    }

    protected function setValue(Field $field, $value)
    {
        $fieldName = $field->getName();
        $this->result[$fieldName]['VALUE'] = $value;

        $specificMethodName = 'set' . $this->getShortClassName($field) . 'Value';

        if(method_exists($this, $specificMethodName))
        {
            $this->$specificMethodName($this->result[$fieldName], $field, $value);
        }
    }

    protected function setEnumFieldValue(&$fieldResult, Field $field, $value)
    {
        if(isset($value))
        {
            $fieldResult['VARIANTS'][$value]['SELECTED'] = true;
        }
        else
        {
            $variantsKeys = array_keys($fieldResult['VARIANTS']);
            $fieldResult['VARIANTS'][$variantsKeys[0]]['SELECTED'] = true;
        }
    }

    protected function setReferenceValue(&$fieldResult, Field $field, $value)
    {
        $this->setEnumFieldValue($fieldResult, $field, $value);
    }

    protected function setBooleanFieldValue(&$fieldResult, BooleanField $field, $value)
    {
        $fieldResult['CHECKED'] = ($value == $fieldResult['TRUE_VALUE']);
    }


    /**
     * Регистрирует запрос значения поля в бд, сам запрос выполняется методом queryValues
     * @param string $targetFieldName - название поля, в которое должно быть записано значение
     * @param string $queryFieldName - название поля, значение которого будет запрошено
     */
    protected function queryValueDeferred($targetFieldName, $queryFieldName = '')
    {
        $queryFieldName = $queryFieldName ?: $targetFieldName;
        $this->queryResultMap[$targetFieldName] = $queryFieldName;
    }

    protected function removeValueQuery($fieldName)
    {
        unset($this->queryResultMap[$fieldName]);
    }

    public function getQueryResultMap()
    {
        return $this->queryResultMap;
    }

    /**
     * Запрашивает значения свойств в базе данных
     * @return \Bitrix\Main\ORM\Query\Result
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function queryValues()
    {
        if($this->params['QUERY_FILTER'])
        {
            $this->query->setFilter($this->params['QUERY_FILTER']);
        }
        elseif($this->params['ENTITY_ID'])
        {
            $this->query->setFilter(array(
                'ID' => $this->params['ENTITY_ID']
            ));
        }
        else
        {
            return null;
        }

        $this->query->setSelect(array_unique(array_values($this->queryResultMap)));
        return $this->query->exec();
    }

    /**
     * Пишет значения полей в результативный массив.
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function writeValues()
    {
        $dbQueryResult = $this->queryValues();
        if($dbQueryResult && ($queryResult = $dbQueryResult->fetch()))
        {
            foreach ($this->queryResultMap as $targetFieldName => $queryFieldName)
            {
                $this->setValue($this->fields[$targetFieldName], $queryResult[$queryFieldName]);
            }
        }
        else
        {
            // пишем значения по умолчанию
            foreach ($this->queryResultMap as $targetFieldName => $queryFieldName)
            {
                $value = ($this->params['PRESET'][$targetFieldName] ?: $this->result[$targetFieldName]['DEFAULT']);
                $this->setValue($this->fields[$targetFieldName], $value);
            }
        }
    }

    public function getResult()
    {
        foreach ($this->externalKeyNames as $keyFieldName)
        {
            unset($this->result[$keyFieldName]);
        }

        return parent::getResult(); // TODO: Change the autogenerated stub
    }

}