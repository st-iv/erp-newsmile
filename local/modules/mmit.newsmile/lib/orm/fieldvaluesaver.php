<?

namespace Mmit\NewSmile\Orm;


use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\MaterialTable;
use Mmit\NewSmile\Orm\Fields\ReverseReference;


/**
 * Сохраняет значения полей сущности в бд
 * Class FieldsValueSaver
 * @package Mmit\NewSmile\Orm
 */
class FieldValueSaver extends FieldsProcessor
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var HttpRequest
     */
    protected $request;
    protected $updateFields = array();

    protected $deferredAddQueries = array();

    public function __construct(Entity $entity, array $params)
    {
        parent::__construct($params);
        $this->entity = $entity;
    }

    public function getUpdateFields()
    {
        return $this->updateFields;
    }

    protected function getRequestParam($paramName)
    {
        return $this->request[$this->params['REQUEST_PARAM_PREFIX'] . $paramName];
    }

    public function setRequest(HttpRequest $request)
    {
        $this->request = $request;
    }

    protected function processField(Field $field)
    {
        $fieldName = $field->getName();
        $isEmptyEditable = empty($this->params['EDITABLE_FIELDS']);

        if($isEmptyEditable || in_array($fieldName, $this->params['EDITABLE_FIELDS']))
        {
            $this->updateFields[$fieldName] = $this->getRequestParam($fieldName);
            $result = true;

        }
        else
        {
            $result = false;
        }

        return $result;
    }

    protected function processBooleanField(BooleanField $field)
    {
        $result = null;

        $fieldName = $field->getName();

        if(empty($this->getRequestParam($fieldName)))
        {
            $values = $field->getValues();
            $this->updateFields[$fieldName] = $values[0];
        }
    }

    protected function processReference(Reference $field)
    {
        $keyFieldName = Helper::getReferenceExternalKeyName($field);
        $this->updateFields[$keyFieldName] = $this->getRequestParam($keyFieldName);
        unset($this->updateFields[$field->getName()]);
    }

    protected function processDatetimeField(DatetimeField $field)
    {
        $fieldName = $field->getName();
        $rawValue = $this->getRequestParam($fieldName);
        $this->updateFields[$fieldName] = DateTime::createFromTimestamp(strtotime($rawValue));
    }

    protected function processReverseReference(ReverseReference $field)
    {
        $sourceEntity = $field->getSourceEntity();
        $keyField = $field->getKeyField();
        $keyFieldName = Helper::getReferenceExternalKeyName($keyField);

        /* подготавливаем параметры для $fieldValueSaver */
        $paramsKey = $sourceEntity->getDataClass() . ':' . $keyField->getName();
        if($paramsKey[0] == '\\')
        {
            $paramsKey = substr($paramsKey, 1);
        }

        $params = $this->params['REVERSE_REFERENCES'][$paramsKey];
        $params['REQUEST_PARAM_PREFIX'] = $field->getName() . '__';
        $params['EDITABLE_FIELDS'] = array_merge($params['EDITABLE_FIELDS'] ?: array(), $sourceEntity->getPrimaryArray());

        /*  проходим сейвером по всем полям привязавшейся сущности и получаем массив полей и их значений для всех элементов,
            привязанных к текущему */

        $fieldValueSaver = new static($sourceEntity, $params);
        $fieldValueSaver->setRequest($this->request);

        foreach ($sourceEntity->getFields() as $subField)
        {
            $fieldValueSaver->process($subField);
        }



        $updateFields = $fieldValueSaver->getUpdateFields();

        /* обновляем существующие элементы и добавляем новые */

        $sourceDataClass = $sourceEntity->getDataClass();

        $itemsFields = array();

        foreach ($updateFields as $fieldName => $fieldValues)
        {
            foreach ($fieldValues as $index => $fieldValue)
            {
                $itemsFields[$index][$fieldName] = $fieldValue;
            }
        }

        $savedItemsPrimaries = array();
        $primaryFieldsNames = $sourceEntity->getPrimaryArray();

        if(!$params['SINGLE_MODE'])
        {
            $allItemsPrimaries = array();

            $dbAllLinkedItems = $sourceDataClass::getList(array(
                'filter' => array(
                    $keyFieldName => $this->params['ENTITY_ID']
                ),
                'select' => $primaryFieldsNames
            ));

            while($item = $dbAllLinkedItems->fetch())
            {
                $primary = array();

                foreach ($primaryFieldsNames as $primaryName)
                {
                    $primary[$primaryName] = $item[$primaryName];
                }

                $allItemsPrimaries[serialize($primary)] = $primary;
            }
        }

        foreach ($itemsFields as $itemFields)
        {
            $primary = array();
            $bPrimaryDefined = true;

            foreach ($primaryFieldsNames as $primaryName)
            {
                if($itemFields[$primaryName])
                {
                    $primary[$primaryName] = $itemFields[$primaryName];
                }
                else
                {
                    $bPrimaryDefined = false;
                    break;
                }
            }

            if($bPrimaryDefined)
            {
                foreach ($primary as $primaryName)
                {
                    unset($itemFields[$primaryName]);
                }

                $sourceDataClass::update($primary, $itemFields);
                $savedItemsPrimaries[serialize($primary)] = true;
            }
            else
            {
                if($this->params['ENTITY_ID'])
                {
                    $itemFields[$keyFieldName] = $this->params['ENTITY_ID'];
                    $sourceDataClass::add($itemFields);
                }
                else
                {
                    // если ENTITY_ID не задан, скорее всего производится добавление основного элемента, поэтому регистрируем
                    // отложенный запрос на добавление, который будет выполнен после добавления основного элемента
                    $itemFields[$keyFieldName] = '#ENTITY_ID#';
                    $this->deferredAddQueries[$sourceDataClass][] = $itemFields;
                }
            }
        }


        /* вычисляем, какие элементы нужно удалить, удаляем */

        if(!$params['SINGLE_MODE'])
        {
            foreach ($allItemsPrimaries as $primaryKey => $primary)
            {
                if(!$savedItemsPrimaries[$primaryKey])
                {
                    $sourceDataClass::delete($primary);
                }
            }
        }

        /* убираем поле из подготовленного запроса на обновление */
        unset($this->updateFields[$field->getName()]);
    }

    public function save()
    {
        $dataManager = $this->entity->getDataClass();

        if(!$this->updateFields) return;

        if($this->params['ENTITY_ID'])
        {
            $dataManager::update($this->params['ENTITY_ID'], $this->updateFields);
        }
        else
        {
            $addResult = $dataManager::add($this->updateFields);
            $this->params['ENTITY_ID'] = $addResult->getId();
        }


        foreach($this->deferredAddQueries as $dataManager => $items)
        {
            foreach ($items as $item)
            {
                foreach ($item as $itemFieldName => &$value)
                {
                    $value = (($value == '#ENTITY_ID#') ? $this->params['ENTITY_ID'] : $value);
                }

                unset($value);

                $dataManager::add($item);
            }
        }
    }
}