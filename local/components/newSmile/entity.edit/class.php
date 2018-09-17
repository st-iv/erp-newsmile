<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader,
    Mmit\NewSmile,
    Bitrix\Main\ORM;


class EntityEditComponent extends \CBitrixComponent
{
    protected $fields;
    protected $mode;

    public function onPrepareComponentParams($arParams)
    {
        $arParams['ENTITY_ID'] = (int)$arParams['ENTITY_ID'];

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

    protected function processSaveRequest(\Bitrix\Main\HttpRequest $request)
    {
        $entityClass = $this->arParams['ENTITY_CLASS'];

        /* определяем, какие поля можно редактировать */

        $fieldsMap = $entityClass::getMap();

        $editFields = array();

        foreach ($fieldsMap as $field)
        {
            /**
             * @var ORM\Fields\Field $field
             */

            $fieldName = $field->getName();
            if($fieldName == 'ID')  continue;

            if(!$this->arParams['EDIT_FIELDS'] || in_array($fieldName, $this->arParams['EDIT_FIELDS']))
            {
                if($this->isReferenceField($field))
                {
                    $editFields[] = NewSmile\Helpers::getReferenceExternalKeyName($field);
                }
                else
                {
                    $editFields[] = $fieldName;
                }
            }
        }

        /* добавляем / обновляем запись */

        $fieldsValues = array();

        foreach($editFields as $fieldName)
        {
            if(isset($request[$fieldName]))
            {
                $fieldsValues[$fieldName] = $request[$fieldName];
            }
        }

        if($fieldsValues)
        {
            if($request['action'] == 'add')
            {
                $entityClass::add($fieldsValues);
            }
            else
            {
                $entityClass::update($this->arParams['ENTITY_ID'], $fieldsValues);
            }
        }
    }
    protected function processDeleteRequest(\Bitrix\Main\HttpRequest $request)
    {
        $entityClass = $this->arParams['ENTITY_CLASS'];
        $entityClass::delete($this->arParams['ENTITY_ID']);
    }

    protected function isReferenceField(ORM\Fields\Field $field)
    {
        return $field instanceof ORM\Fields\Relations\Reference;
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
            ShowError('Group id not specified');
            $isSuccess = false;
        }

        return $isSuccess;
    }

    protected function prepareResult()
    {
        $this->fields = $this->getFields();

        if($this->getMode() == 'update')
        {
            $this->writeFieldsValues();
        }

        $this->arResult['FIELDS'] = $this->fields;

        $this->arResult['ACTION'] = $this->getMode();
    }

    /**
     * Формирует массив полей на основе параметров EDIT_FIELDS и SHOW_FIELDS. В массив полей попадают только те поля,
     * которые действительно есть в сущности
     *
     * @return array
     */
    protected function getFields()
    {
        $fields = array();
        $entityClass = $this->arParams['ENTITY_CLASS'];

        foreach ($entityClass::getMap() as $field)
        {
            /**
             * @var ORM\Fields\Field $field
             */

            $fieldName = $field->getName();
            $bCanEdit = !$this->arParams['EDIT_FIELDS'] || in_array($fieldName, $this->arParams['EDIT_FIELDS']);
            $bCanShow = $bCanEdit || (!$this->arParams['SHOW_FIELDS'] || in_array($fieldName, $this->arParams['SHOW_FIELDS']));

            if($bCanShow)
            {
                $fields[$fieldName] = $this->getFieldArray($field, $bCanEdit);
            }
        }

        return $fields;
    }

    protected function getFieldArray(ORM\Fields\Field $field, $bEditable)
    {
        $fieldClassName = get_class($field);
        $fieldClassShortName = substr($fieldClassName, strrpos($fieldClassName, '\\') + 1);
        $fieldType = strtolower(str_replace('Field', '', $fieldClassShortName));

        $result = array(
            'NAME' => $field->getName(),
            'TITLE' => $field->getTitle(),
            'TYPE' => $fieldType,
            'EDITABLE' => $bEditable,
        );

        if($field instanceof ORM\Fields\ScalarField)
        {
            $result['REQUIRED'] = $field->isRequired();
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
            $result['REFERENCE_ITEMS'] = $this->getReferenceElements($field);
            $result['INPUT_NAME'] = $result['REFERENCE_KEY_NAME'];

            $externalKeyField = $this->getFieldByName($this->arParams['ENTITY_CLASS'], $result['REFERENCE_KEY_NAME']);

            if($externalKeyField !== null)
            {
                $result['TITLE'] = $externalKeyField->getTitle();

                if($externalKeyField instanceof ORM\Fields\ScalarField)
                {
                    $result['REQUIRED'] = $externalKeyField->isRequired();
                }
            }
        }
        else
        {
            $result['INPUT_NAME'] = $result['NAME'];
        }


        return $result;
    }

    protected function writeFieldValue($fieldName, $fieldValue, $referenceFieldsMap)
    {
        if(isset($this->fields[$fieldName]))
        {
            $this->fields[$fieldName]['VALUE'] = $fieldValue;
        }
        elseif ($referenceFieldsMap[$fieldName] && $fieldValue)
        {
            $referenceFieldName = $referenceFieldsMap[$fieldName];
            $this->fields[$referenceFieldName]['REFERENCE_ITEMS'][$fieldValue]['SELECTED'] = true;
            $this->fields[$referenceFieldName]['VALUE'] = $fieldValue;
        }
    }

    /**
     * Получает значения полей, попутно запрашивает связанные элементы
     */
    protected function writeFieldsValues()
    {
        $entityClass = $this->arParams['ENTITY_CLASS'];

        $select = array();
        $referencesEntities = array();

        $referencesFieldsMap = array();

        foreach($this->fields as $field)
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

        /* запрашиваем связанные элементы */
        $this->queryReferences($referencesEntities);

        /* запрашиваем значения полей */

        $select[] = 'ID';

        $dbEntity = $entityClass::getList(array(
            'filter' => array(
                'ID' => $this->arParams['ENTITY_ID']
            ),
            'select' => $select
        ));

        if($entity = $dbEntity->fetch())
        {
            foreach($entity as $fieldName => $fieldValue)
            {
                $this->writeFieldValue($fieldName, $fieldValue, $referencesFieldsMap);
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



    /**
     * Запрашивает элементы указанных сущностей, заносит элементы в REFERENCE_ITEMS
     * @param array $entities
     */
    protected function queryReferences(array $entities)
    {
        foreach ($entities as $entityClass => $entity)
        {
            if(NewSmile\Helpers::isOrmEntityClass($entityClass) && $entity['FIELDS'])
            {
                $entity['FIELDS'][] = 'ID';
                $dbElements = $entityClass::getList(array(
                    'select' => $entity['FIELDS']
                ));

                while($element = $dbElements->fetch())
                {
                    if($element['ID'] )
                    $this->fields[$entity['REFERENCE_FIELD_NAME']]['REFERENCE_ITEMS'][$element['ID']] = $element;
                }
            }
        }
    }

    protected function getReferenceElements(ORM\Fields\Relations\Reference $referenceField)
    {
        $result = array();

        $entityClass = $referenceField->getRefEntity()->getDataClass();
        $select = $this->getReferenceSelectFields($referenceField);

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

    protected function getReferenceSelectFields(ORM\Fields\Relations\Reference $referenceField)
    {
        $selectFields = array();

        if($this->arParams['SHOW_FIELDS'])
        {
            $referenceFieldName = $referenceField->getName();

            foreach ($this->arParams['SHOW_FIELDS'] as $showFieldName)
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