<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader,
    Mmit\NewSmile,
    Bitrix\Main\ORM,
    Bitrix\Main\Type\DateTime;


class EntityEditComponent extends \CBitrixComponent
{
    protected $fields;
    protected $mode;

    public function onPrepareComponentParams($arParams)
    {
        \CModule::IncludeModule('mmit.newsmile');

        $arParams['ENTITY_ID'] = (int)$arParams['ENTITY_ID'];


        $reverseReferences = array();

        foreach ($arParams['REVERSE_REFERENCES'] as $entityKeyField => $reverseReferenceParams)
        {
            if (preg_match('/^([A-z0-9\\\\]+):([A-Z0-9_]+)$/', $entityKeyField, $matches))
            {
                if(NewSmile\Helpers::isOrmEntityClass($matches[1]))
                {
                    $reverseReferences[$entityKeyField] = $reverseReferenceParams;
                    $reverseReferences[$entityKeyField]['CLASS'] = $matches[1];
                    $reverseReferences[$entityKeyField]['KEY_FIELD_NAME'] = $matches[2];
                }
            }
        }

        $arParams['REVERSE_REFERENCES'] = $reverseReferences;

        return $arParams;
    }

    protected function processRequest(\Bitrix\Main\HttpRequest $request)
    {
        if($request->isEmpty() || !$request->isPost() || !$request['action'] || !check_bitrix_sessid())
        {
            //return;
        }

        switch($request['action'])
        {
            case 'add':
            case 'update':
                $this->processSaveRequest($request);
                break;

            case 'delete':
                $this->processDeleteRequest($request);
                break;
        }
    }

    protected function getFieldsForEdit($entityClass, $editableFieldsParam)
    {
        $fieldsForUpdate = array();

        foreach ($entityClass::getMap() as $field)
        {
            /**
             * @var ORM\Fields\Field $field
             */

            $fieldName = $field->getName();
            if($fieldName == 'ID')  continue;

            if(!$editableFieldsParam || in_array($fieldName, $editableFieldsParam))
            {
                $editField = array(
                    'TYPE' => $this->getFieldType($field)
                );

                if($this->isReferenceField($field))
                {
                    $editField['NAME'] = NewSmile\Helpers::getReferenceExternalKeyName($field);
                }
                else
                {
                    $editField['NAME'] = $fieldName;
                }

                if($editField['TYPE'] == 'boolean')
                {
                    $editField['VALUES'] = $field->getValues();
                }

                $fieldsForUpdate[] = $editField;
            }
        }

        return $fieldsForUpdate;
    }

    protected function prepareValueForSave($fieldInfo, $fieldValue)
    {
        switch($fieldInfo['TYPE'])
        {
            case 'boolean':
                if(empty($fieldValue))
                {
                    $fieldValue = $fieldInfo['VALUES'][0];
                }
                break;

            case 'datetime':
                $fieldValue = DateTime::createFromTimestamp(strtotime($fieldValue));
                break;
        }

        return $fieldValue;
    }

    protected function processSaveRequest(\Bitrix\Main\HttpRequest $request)
    {
        $entityClass = $this->arParams['ENTITY_CLASS'];

        /* определяем, какие поля можно редактировать */

        $editFields = $this->getFieldsForEdit($entityClass, $this->arParams['EDIT_FIELDS']);

        /* добавляем / обновляем запись */

        $fieldsValues = array();

        foreach($editFields as $editField)
        {
            if(isset($request[$editField['NAME']]) || ($editField['TYPE'] == 'boolean'))
            {
                $fieldValue = $request[$editField['NAME']];
                $fieldsValues[$editField['NAME']] = $this->prepareValueForSave($editField, $fieldValue);
            }
        }

        $entityId = $this->arParams['ENTITY_ID'];

        if($fieldsValues)
        {
            if($request['action'] == 'add')
            {
                /**
                 * @var \Bitrix\Main\Entity\AddResult $addResult
                 */
                $addResult = $entityClass::add($fieldsValues);
                $entityId = $addResult->getId();
            }
            else
            {
                $entityClass::update($this->arParams['ENTITY_ID'], $fieldsValues);
            }
        }

        if($this->arParams['REVERSE_REFERENCES'])
        {
            $this->saveReverseReferences($request, $entityId);
        }
    }
    protected function processDeleteRequest(\Bitrix\Main\HttpRequest $request)
    {
        $entityClass = $this->arParams['ENTITY_CLASS'];
        $entityClass::delete($this->arParams['ENTITY_ID']);
    }

    protected function saveReverseReferences(\Bitrix\Main\HttpRequest $request, $entityId)
    {
        foreach ($this->arParams['REVERSE_REFERENCES'] as $reverseReferenceParams)
        {
            $entityClass = $reverseReferenceParams['CLASS'];
            $keyFieldName = $reverseReferenceParams['KEY_FIELD_NAME'];

            $editFields = $this->getFieldsForEdit($entityClass, $reverseReferenceParams['EDIT_FIELDS']);

            $editFields[] = array(
                'NAME' => 'ID'
            );

            $fieldsValues = array();

            foreach($editFields as $editField)
            {
                $inputName = $this->getReverseRefInputName($entityClass, $editField['NAME']);
                if(isset($request[$inputName]) || ($editField['TYPE'] == 'boolean'))
                {
                    $fieldValues = $request[$inputName];

                    foreach ($fieldValues as $index => $fieldValue)
                    {
                        $fieldsValues[$index][$editField['NAME']] = $this->prepareValueForSave($editField, $fieldValue);
                    }
                }
            }

            $dbAllElements = $entityClass::getList(array(
                'filter' => array(
                    $keyFieldName => $entityId
                ),
                'select' => array('ID')
            ));

            $allElementsIds = array();

            while($element = $dbAllElements->fetch())
            {
                $allElementsIds[] = $element['ID'];
            }

            $savedElementsIds = array();

            foreach ($fieldsValues as $itemFieldsValues)
            {
                $itemId = $itemFieldsValues['ID'];
                unset($itemFieldsValues['ID']);

                if(!$itemId)
                {
                    $itemFieldsValues[$keyFieldName] = $entityId;
                    $entityClass::add($itemFieldsValues);
                }
                else
                {
                    $entityClass::update($itemId, $itemFieldsValues);
                    $savedElementsIds[] = $itemId;
                }
            }

            foreach (array_diff($allElementsIds, $savedElementsIds) as $itemId)
            {
                $entityClass::delete($itemId);
            }
        }
    }

    protected function isReferenceField(ORM\Fields\Field $field)
    {
        return $field instanceof ORM\Fields\Relations\Reference;
    }

    protected function getFieldType($field)
    {
        $fieldClassName = get_class($field);
        $fieldClassShortName = substr($fieldClassName, strrpos($fieldClassName, '\\') + 1);
        return strtolower(str_replace('Field', '', $fieldClassShortName));
    }

    protected function checkParams()
    {
        $isSuccess = true;

        if(!NewSmile\Helpers::isOrmEntityClass($this->arParams['ENTITY_CLASS']))
        {
            ShowError('Group entity is not found');
            $isSuccess = false;
        }

        if(!$this->arParams['ENTITY_ID'] && ($this->getMode() !== 'add'))
        {
            ShowError('Group id is not specified');
            $isSuccess = false;
        }

        return $isSuccess;
    }

    protected function prepareResult()
    {
        $this->fields = $this->getFields($this->arParams['ENTITY_CLASS'], array(
            'SHOW_FIELDS' => $this->arParams['SHOW_FIELDS'],
            'EDIT_FIELDS' => $this->arParams['EDIT_FIELDS'],
        ));

        $this->writeFieldsValues($this->arParams['ENTITY_CLASS'], $this->fields);
        $this->arResult['FIELDS'] = $this->fields;

        $this->arResult['REVERSE_REFERENCES'] = $this->getReverseReferences();

        $this->arResult['ACTION'] = $this->getMode();
    }

    protected function getReverseRefInputName($entityClass, $standardInputName)
    {
        return strtoupper(str_replace('\\', '_', $entityClass)) . '_' . $standardInputName;
    }

    protected function getReverseReferences()
    {
        $reverseReferences = array();

        foreach ($this->arParams['REVERSE_REFERENCES'] as $reverseReferenceParams)
        {
            $entityFieldClass = $reverseReferenceParams['CLASS'];
            $entityKeyFieldName = $reverseReferenceParams['KEY_FIELD_NAME'];

            $entityFields = $this->getFields($entityFieldClass, array(
                'SHOW_FIELDS' => $reverseReferenceParams['SHOW_FIELDS'],
                'EDIT_FIELDS' => $reverseReferenceParams['EDIT_FIELDS'],
            ));


            $entityFields['ID'] = array(
                'TYPE' => 'hidden',
                'NAME' => 'ID',
                'INPUT_NAME' => 'ID',
            );

            foreach ($entityFields as $entityFieldName => &$entityField)
            {
                $entityField['INPUT_NAME'] = $this->getReverseRefInputName($entityFieldClass, $entityField['INPUT_NAME']) . '[]';

                // удаляем из полученного массива полей reference поле, по которому осуществляется обратный reference
                if(($entityField['NAME'] == $entityKeyFieldName) || ($entityField['REFERENCE_KEY_NAME'] == $entityKeyFieldName))
                {
                    unset($entityFields[$entityFieldName]);
                }
            }

            unset($entityField);

            $select = array();

            $referencesFieldsMap = array();

            foreach($entityFields as $entityField)
            {
                if($entityField['TYPE'] == 'reference')
                {
                    $referencesFieldsMap[$entityField['REFERENCE_KEY_NAME']] = $entityField['NAME'];
                    $select[] = $entityField['REFERENCE_KEY_NAME'];
                }
                else
                {
                    $select[] = $entityField['NAME'];
                }
            }

            /* запрашиваем значения полей */

            $dbEntity = $entityFieldClass::getList(array(
                'filter' => array(
                    $entityKeyFieldName => $this->arParams['ENTITY_ID']
                ),
                'select' => $select
            ));

            /**
             * @var Bitrix\Main\DB\Result $dbEntity
             */

            $reverseReferences[$entityFieldClass] = array(
                'TITLE' => $reverseReferenceParams['TITLE'],
                'FIELDS' => $entityFields,
                'ITEMS' => array(),
            );

            $reverseReferenceItems =& $reverseReferences[$entityFieldClass]['ITEMS'];

            while($element = $dbEntity->fetch())
            {
                foreach($element as $fieldName => $fieldValue)
                {
                    if(isset($entityFields[$fieldName]))
                    {
                        $reverseReferenceItems[$element['ID']][$fieldName] = $fieldValue;
                    }
                    elseif ($referencesFieldsMap[$fieldName] && $fieldValue)
                    {
                        // если поля с этим именем нет в массиве полей для вывода, это может быть значение поля reference
                        $referenceFieldName = $referencesFieldsMap[$fieldName];
                        $reverseReferenceItems[$element['ID']][$referenceFieldName] = $fieldValue;
                    }
                }
            }
        }

        return $reverseReferences;
    }

    /**
     * Формирует массив полей на основе параметров EDIT_FIELDS и SHOW_FIELDS. В массив полей попадают только те поля,
     * которые действительно есть в сущности
     *
     * @return array
     */
    protected function getFields($entityClass, array $params = array())
    {
        $fields = array();

        foreach ($entityClass::getMap() as $field)
        {
            /**
             * @var ORM\Fields\Field $field
             */

            $fieldName = $field->getName();
            if($fieldName == 'ID') continue;

            $bCanEdit = !$params['EDIT_FIELDS'] || in_array($fieldName, $params['EDIT_FIELDS']);
            $bCanShow = $bCanEdit || (!$params['SHOW_FIELDS'] || in_array($fieldName, $params['SHOW_FIELDS']));

            if($bCanShow)
            {
                $fields[$fieldName] = $this->getFieldArray($field, $bCanEdit, $entityClass, $params);
            }
        }

        return $fields;
    }

    protected function getFieldArray(ORM\Fields\Field $field, $bEditable, $entityClass, array $params)
    {
        $result = array(
            'NAME' => $field->getName(),
            'TITLE' => $field->getTitle(),
            'TYPE' => $this->getFieldType($field),
            'EDITABLE' => $bEditable,
        );

        if($field instanceof ORM\Fields\BooleanField)
        {
            $fieldValues = $field->getValues();
            $result['TRUE_VALUE'] = $fieldValues[1];
        }

        if($field instanceof ORM\Fields\ScalarField)
        {
            $result['REQUIRED'] = $field->isRequired();
            $result['DEFAULT'] = $field->getDefaultValue();
        }
        

        if($field instanceof ORM\Fields\Relations\Reference)
        {
            $referenceClassName = $field->getRefEntity()->getDataClass();
            if($referenceClassName[0] == '\\')
            {
                $referenceClassName = substr($referenceClassName, 1);
            }

            $result['REFERENCE_ENTITY_CLASS'] = $referenceClassName;
            $result['REFERENCE_KEY_NAME'] = NewSmile\Helpers::getReferenceExternalKeyName($field);
            $result['INPUT_NAME'] = $result['REFERENCE_KEY_NAME'];
            $result['REFERENCE_ITEMS'] = $this->getReferenceElements($field, $params);

            $externalKeyField = $this->getFieldByName($entityClass, $result['REFERENCE_KEY_NAME']);

            if($externalKeyField !== null)
            {
                $result['TITLE'] = $externalKeyField->getTitle();
                $result['DEFAULT'] = $externalKeyField->getDefaultValue();

                if($externalKeyField instanceof ORM\Fields\ScalarField)
                {
                    $result['REQUIRED'] = $externalKeyField->isRequired();
                    if($result['REQUIRED'] && !$result['DEFAULT'] && $result['REFERENCE_ITEMS'])
                    {
                        $referenceItems = $result['REFERENCE_ITEMS'];
                        $result['DEFAULT'] = array_shift($referenceItems)['ID'];
                    }
                }
            }
        }
        else
        {
            $result['INPUT_NAME'] = $result['NAME'];
        }


        return $result;
    }

    /**
     * Сохраняет значение поля в массив полей для вывода, на основе значения выставляет ключи SELECTED и CHECKED
     * @param $fieldName
     * @param $fieldValue
     * @param $fields
     * @param array $referenceFieldsMap
     */
    protected function writeFieldValue($fieldName, $fieldValue, &$fields, array $referenceFieldsMap = array())
    {
        if(isset($fields[$fieldName]))
        {
            $fields[$fieldName]['VALUE'] = $fieldValue;
        }
        elseif ($referenceFieldsMap[$fieldName] && $fieldValue)
        {
            // если поля с этим именем нет в массиве полей для вывода, это может быть значение поля reference

            $referenceFieldName = $referenceFieldsMap[$fieldName];
            $fields[$referenceFieldName]['REFERENCE_ITEMS'][$fieldValue]['SELECTED'] = true;
            $fields[$referenceFieldName]['VALUE'] = $fieldValue;
        }

        $fieldType = $fields[$fieldName]['TYPE'];
        if($fieldType == 'boolean')
        {
            $fields[$fieldName]['CHECKED'] = ($fieldValue == $fields[$fieldName]['TRUE_VALUE']);
        }
    }

    /**
     * Получает значения полей, попутно запрашивает связанные элементы
     */
    protected function writeFieldsValues($entityClass, array &$fields)
    {
        if($this->getMode() == 'add')
        {
            foreach ($fields as $field)
            {
                $this->writeFieldValue($field['NAME'], $field['DEFAULT'], $fields);
            }

            return;
        }

        $select = array();

        $referencesFieldsMap = array();

        foreach($fields as $field)
        {
            if($field['TYPE'] == 'reference')
            {
                $referencesFieldsMap[$field['REFERENCE_KEY_NAME']] = $field['NAME'];
                $select[] = $field['REFERENCE_KEY_NAME'];
            }
            else
            {
                $select[] = $field['NAME'];
            }
        }

        /* запрашиваем значения полей */

        $select[] = 'ID';

        $dbEntity = $entityClass::getList(array(
            'filter' => array(
                'ID' => $this->arParams['ENTITY_ID']
            ),
            'select' => $select
        ));

        /**
         * @var Bitrix\Main\DB\Result $dbEntity
         */

        if($entity = $dbEntity->fetch())
        {
            foreach($entity as $fieldName => $fieldValue)
            {
                $this->writeFieldValue($fieldName, $fieldValue, $fields, $referencesFieldsMap);
            }
        }
    }

    protected function getFieldByName($entityClass, $fieldName)
    {
        $resultField = null;

        foreach($entityClass::getMap() as $field)
        {
            /**
             * @var \Bitrix\Main\Entity\Field $field
             */

            if($field->getName() == $fieldName)
            {
                $resultField = $field;
                break;
            }
        }

        return $resultField;
    }


    protected function getReferenceElements(ORM\Fields\Relations\Reference $referenceField, array $params)
    {
        $result = array();

        $entityClass = $referenceField->getRefEntity()->getDataClass();
        $select = $this->getReferenceSelectFields($referenceField, $params['SHOW_FIELDS']);

        if(NewSmile\Helpers::isOrmEntityClass($entityClass) && $select)
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

    protected function getReferenceSelectFields(ORM\Fields\Relations\Reference $referenceField, $showFields)
    {
        $selectFields = array();

        if($showFields)
        {
            $referenceFieldName = $referenceField->getName();

            foreach ($showFields as $showFieldName)
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

    protected function getMode()
    {
        if(!$this->mode)
        {
            $this->mode = $this->request->getPost('action');

            if(!$this->mode)
            {
                global $APPLICATION;
                $this->mode = (($this->arParams['ADD_URL'] == $APPLICATION->GetCurPage()) ? 'add' : 'update');
            }

        }

        return $this->mode;
    }


	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
        if (!Loader::includeModule('mmit.newSmile'))
        {
            ShowError('Module mmit.newsmile is not installed');
        }

        if($this->checkParams())
        {
            $this->processRequest($this->request);

            if($this->getMode() != 'delete')
            {
                $this->prepareResult();
            }

            $this->includeComponentTemplate();
        }
	}
}
?>