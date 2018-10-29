<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Mmit\NewSmile;
class EntityListComponent extends \CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams['SECTION_ID'] = (int)$arParams['SECTION_ID'];
        $arParams['GROUP_FIELDS'] = (is_array($arParams['GROUP_FIELDS']) ? $arParams['GROUP_FIELDS'] : array());
        $arParams['ELEMENT_FIELDS'] = (is_array($arParams['ELEMENT_FIELDS']) ? $arParams['ELEMENT_FIELDS'] : array());

        /* добавляем алиасы для полей reference, это нужно для сопоставления полей и их значений */
        foreach (['ELEMENT', 'GROUP'] as $objectType)
        {
            foreach ($arParams[$objectType . '_FIELDS'] as $index => $selectFieldName)
            {
                if(preg_match('/^([A-Z0-9_]+)\.([A-Z0-9_]+)$/', $selectFieldName, $matches))
                {
                    unset($arParams[$objectType . '_FIELDS'][$index]);
                    $alias = $this->getRefFieldAlias($matches[1], $matches[2]);
                    $arParams[$objectType . '_FIELDS'][$alias] = $selectFieldName;
                }
            }
        }

        return $arParams;
    }

    protected function getRefFieldAlias($refName, $fieldName)
    {
        return $refName . '__' . $fieldName;
    }

    protected function checkParams()
    {
        $isSuccess = true;

        if($this->arParams['DATA_MANAGER_CLASS_ELEMENT'] && !class_exists($this->arParams['DATA_MANAGER_CLASS_ELEMENT']))
        {
            ShowError('Element data manager is not found');
            $isSuccess = false;
        }

        if($this->arParams['DATA_MANAGER_CLASS_GROUP'] && !class_exists($this->arParams['DATA_MANAGER_CLASS_GROUP']))
        {
            ShowError('Section data manager is not found');
            $isSuccess = false;
        }

        return $isSuccess;
    }

    protected function prepareResult()
    {
        if($this->arParams['DATA_MANAGER_CLASS_GROUP'])
        {
            $this->arResult['SECTIONS'] = $this->getSections();
            $this->arResult['GROUP_FIELDS'] = $this->getFieldsInfo('GROUP');
        }

        if($this->arParams['DATA_MANAGER_CLASS_ELEMENT'])
        {
            $this->arResult['ELEMENTS'] = $this->getElements();
            $this->arResult['ELEMENT_FIELDS'] = $this->getFieldsInfo('ELEMENT');
        }

        if(!$this->arParams['SECTION_ID'] && $this->arResult['SECTIONS'])
        {
            $this->arResult['SECTIONS'] = NewSmile\Helpers::getTree($this->arResult['SECTIONS']);
        }
    }


    protected function getSections()
    {
        $sections = array();

        $sectionDataManager = $this->arParams['DATA_MANAGER_CLASS_GROUP'];

        if($sectionDataManager)
        {
            $filter = array();
            $parentSectionFieldName = $this->getReferenceFieldName($sectionDataManager::getEntity(), $sectionDataManager);

            if($this->arParams['SECTION_ID'])
            {
                if($parentSectionFieldName)
                {
                    $filter[$parentSectionFieldName] = $this->arParams['SECTION_ID'];
                }
            }

            if($this->arParams['GROUP_FIELDS'])
            {
                $select = $this->arParams['GROUP_FIELDS'];
                $select[] = 'ID';

                if($parentSectionFieldName)
                {
                    $select[] = $parentSectionFieldName;
                }
            }
            else
            {
                $select = array('*');
            }

            $dbSections = $sectionDataManager::getList(array(
                'filter' => $filter,
                'select' => $select
            ));

            while ($section = $dbSections->fetch())
            {
                $section['URL'] = \CComponentEngine::makePathFromTemplate($this->arParams['SECTION_URL'], array(
                    'SECTION_ID' => $section['ID']
                ));

                $section['EDIT_URL'] = \CComponentEngine::makePathFromTemplate($this->arParams['SECTION_EDIT_URL'], array(
                    'SECTION_ID' => $section['ID']
                ));

                $sections[$section['ID']] = $section;
            }
        }

        return $sections;
    }

    protected function getElements()
    {
        $elementDataManager = $this->arParams['DATA_MANAGER_CLASS_ELEMENT'];

        $filter = array();
        if($this->arParams['SECTION_ID'] && $this->arParams['DATA_MANAGER_CLASS_GROUP'])
        {
            $parentSectionFieldName = $this->getReferenceFieldName(
                $elementDataManager::getEntity(),
                $this->arParams['DATA_MANAGER_CLASS_GROUP']
            );

            if($parentSectionFieldName)
            {
                $filter[$parentSectionFieldName] = $this->arParams['SECTION_ID'];
            }
        }

        $select = ($this->arParams['ELEMENT_FIELDS']
            ? array_merge($this->arParams['ELEMENT_FIELDS'], array('ID'))
            : array());

        $dbElements = $elementDataManager::getList(array(
            'filter' => $filter,
            'select' => $select
        ));

        $elements = array();

        while ($element = $dbElements->fetch())
        {
            $element['NAME_BY_TEMPLATE'] = $this->getNameByTemplate($element);

            if($this->arParams['ELEMENT_URL'])
            {
                $element['URL'] = \CComponentEngine::makePathFromTemplate($this->arParams['ELEMENT_URL'], array(
                    'SECTION_ID' => $this->arParams['SECTION_ID'],
                    'ELEMENT_ID' => $element['ID']
                ));
            }


            if($this->arParams['ELEMENT_EDIT_URL'])
            {
                $element['EDIT_URL'] = \CComponentEngine::makePathFromTemplate($this->arParams['ELEMENT_EDIT_URL'], array(
                    'SECTION_ID' => $this->arParams['SECTION_ID'],
                    'ELEMENT_ID' => $element['ID'],
                ));
            }


            $elements[] = $element;
        }

        return $elements;
    }

    protected function getFieldsInfo($objectType)
    {
        $fieldsInfo = [];

        $dataManager = $this->arParams['DATA_MANAGER_CLASS_' . $objectType];

        if(!NewSmile\Orm\Helper::isDataManagerClass($dataManager))
        {
            ShowError('Не найден DataManager класс ' . $dataManager);
            return [];
        }

        /**
         * @var \Bitrix\Main\Entity\DataManager $dataManager
         */

        $selectedFields = $this->arParams[$objectType . '_FIELDS'] ?: [];

        $bAllFieldsSelected = !$selectedFields || in_array('*', $selectedFields);


        $allFields = $dataManager::getEntity()->getFields();
        $referenceFields = [];

        foreach ($allFields as $field)
        {
            $fieldType = NewSmile\Orm\Helper::getFieldType($field);
            $fieldName = $field->getName();

            if($bAllFieldsSelected || in_array($fieldName, $selectedFields))
            {
                $fieldsInfo[$fieldName] = [
                    'TITLE' => $field->getTitle(),
                    'TYPE' => $fieldType,
                    'VALUE_KEY' => $fieldName,
                    'SERIALIZED' => $field->isSerialized()
                ];


                if($fieldType == 'reference')
                {
                    $referenceFields[] = $field;
                }
            }
        }

        foreach ($referenceFields as $referenceField)
        {
            $refKeyName = NewSmile\Orm\Helper::getReferenceExternalKeyName($referenceField);
            $refFieldName = $referenceField->getName();

            unset($fieldsInfo[$refKeyName]);

            $refSelectedFields = $this->getRefSelectedFields($referenceField, $selectedFields);

            if($refSelectedFields)
            {
                $fieldsInfo[$refFieldName]['VALUE_KEY'] = $this->getRefFieldAlias($refFieldName, array_pop($refSelectedFields));
            }
            else
            {
                ShowError('Для reference поля ' . $refFieldName . ' не указаны запрашиваемые поля связанной таблицы');
                unset($fieldsInfo[$refFieldName]);
            }


        }


        return $fieldsInfo;
    }

    protected function getRefSelectedFields(\Bitrix\Main\Entity\ReferenceField $referenceField, array $selectedFields)
    {
        $refSelectedFields = [];
        $referenceFieldName = $referenceField->getName();
        foreach ($selectedFields as $selectedFieldName)
        {
            if(preg_match('/^' . $referenceFieldName . '\.([A-Z_0-9]+)$/', $selectedFieldName, $matches))
            {
                $refSelectedFields[] = $matches[1];
            }
        }

        return $refSelectedFields;
    }

    protected function getNameByTemplate(array $elementFields)
    {
        if(preg_match_all('/(#([A-Z0-9_]+)#)+/', $this->arParams['ELEMENT_NAME_TEMPLATE'], $matches))
        {
            $replaces = array();

            foreach ($matches[2] as $fieldName)
            {
                $replaces[] = $elementFields[$fieldName];
            }

            $nameByTemplate = str_replace($matches[1], $replaces, $this->arParams['ELEMENT_NAME_TEMPLATE']);
        }
        elseif(count($elementFields) === 1)
        {
            $nameByTemplate = array_pop($elementFields);
        }
        else
        {
            $nameByTemplate = '';

            foreach ($elementFields as $fieldName)
            {
                if($fieldName != 'ID')
                {
                    $nameByTemplate = $fieldName;
                    break;
                }
            }
        }

        return $nameByTemplate;
    }

    /**
     * Получает название поля - внешнего ключа, связывающего $needleEntity с $referenceEntity
     * @param string $needleEntity - класс orm сущности (вместе с Table), в которой будет осуществляться поиск ключа
     * @param string $referenceClass - класс orm сущности (вместе с Table), привязанная к $needleEntity искомым внешним
     * ключом
     *
     * @return string
     */
    protected function getReferenceFieldName(\Bitrix\Main\ORM\Entity $needleEntity, $referenceClass)
    {
        $fieldName = '';

        try
        {
            $map = $needleEntity->getFields();

            foreach ($map as $field)
            {
                if($field instanceof Bitrix\Main\ORM\Fields\Relations\Reference)
                {
                    $curRefEntityClass = $field->getRefEntity()->getDataClass();

                    if($curRefEntityClass[0] === '\\')
                    {
                        $curRefEntityClass = substr($curRefEntityClass, 1);
                    }

                    if($curRefEntityClass === $referenceClass)
                    {
                        $fieldName = $field->getName() . '_ID';
                        break;
                    }
                }
            }
        }
        catch(\Exception $e) {}

        return $fieldName;
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
            $this->prepareResult();
            $this->includeComponentTemplate();
        }
	}
}
?>