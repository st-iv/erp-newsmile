<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader,
    Mmit\NewSmile,
    Bitrix\Main\ORM,
    Bitrix\Main\Type\DateTime;


class EntityEditComponent extends \CBitrixComponent
{
    /**
     * @var \Bitrix\Main\Entity\Field[]
     */
    protected $fields;
    protected $mode;
    protected $mainTemplateFolder;
    protected $originalTemplateFolder;

    /**
     * @var \Bitrix\Main\Entity\DataManager
     */
    protected $mainDataManager;

    /**
     * @var NewSmile\Orm\FieldArrayConstructor
     */
    protected $fieldArrayConstructor;

    /**
     * @var NewSmile\Orm\FieldValueSaver
     */
    protected $fieldValueSaver;

    public function onPrepareComponentParams($arParams)
    {
        \CModule::IncludeModule('mmit.newsmile');

        $arParams['ENTITY_ID'] = (int)$arParams['ENTITY_ID'];

        if(!$arParams['ENTITY_ID'] && !$arParams['ADD_URL'])
        {
            $arParams['ADD_URL'] = $GLOBALS['APPLICATION']->GetCurPage();
        }


        $reverseReferences = array();

        foreach ($arParams['REVERSE_REFERENCES'] as $entityKeyField => $reverseReferenceParams)
        {
            if (preg_match('/^([A-z0-9\\\\]+):([A-Z0-9_]+)$/', $entityKeyField, $matches))
            {
                if(NewSmile\Orm\Helper::isDataManagerClass($matches[1]))
                {
                    $reverseReferences[$entityKeyField] = $reverseReferenceParams;
                    $reverseReferences[$entityKeyField]['CLASS'] = $matches[1];
                    $reverseReferences[$entityKeyField]['KEY_FIELD_NAME'] = $matches[2];
                }
            }
        }

        $arParams['REVERSE_REFERENCES'] = $reverseReferences;


        if($arParams['FIELD_ARRAY_CONSTRUCTOR'] instanceof NewSmile\Orm\FieldArrayConstructor)
        {
            $this->fieldArrayConstructor = $arParams['FIELD_ARRAY_CONSTRUCTOR'];
        }
        else
        {
            $this->fieldArrayConstructor = new NewSmile\Orm\FieldArrayConstructor(
                $arParams['DATA_MANAGER_CLASS']::getEntity(),
                array()
            );
        }

        $this->fieldArrayConstructor->setParam('EDITABLE_FIELDS', $arParams['EDITABLE_FIELDS']);
        $this->fieldArrayConstructor->setParam('REVERSE_REFERENCES', $arParams['REVERSE_REFERENCES']);
        $this->fieldArrayConstructor->setParam('ENTITY_ID', $arParams['ENTITY_ID']);

        if($arParams['FIELD_VALUE_SAVER'] instanceof NewSmile\Orm\FieldValueSaver)
        {
            $this->fieldValueSaver = $arParams['FIELD_VALUE_SAVER'];
        }
        else
        {
            $this->fieldValueSaver = new NewSmile\Orm\FieldValueSaver(
                $arParams['DATA_MANAGER_CLASS']::getEntity(),
                array()
            );
        }

        $this->fieldValueSaver->setParam('REVERSE_REFERENCES', $arParams['REVERSE_REFERENCES']);
        $this->fieldValueSaver->setParam('ENTITY_ID', $arParams['ENTITY_ID']);

        return $arParams;
    }

    protected function processRequest(\Bitrix\Main\HttpRequest $request)
    {
        if($request->isEmpty() || !$request->isPost() || !$request['action'] || !check_bitrix_sessid())
        {
            return;
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
        $editableFields = $this->arParams['EDITABLE_FIELDS'];

        foreach ($this->fields as $field)
        {
            if($field instanceof NewSmile\Orm\Fields\ReverseReference)
            {
                $editableFields[] = $field->getName();
            }
        }

        $this->fieldValueSaver->setRequest($request);
        $this->fieldValueSaver->setParam('EDITABLE_FIELDS', $editableFields);

        foreach ($this->fields as $field)
        {
            if($field->getName() != 'ID')
            {
                $this->fieldValueSaver->process($field);
            }
        }

        $this->fieldValueSaver->save();
    }


    protected function processDeleteRequest(\Bitrix\Main\HttpRequest $request)
    {
        $entityClass = $this->arParams['DATA_MANAGER_CLASS'];
        $entityClass::delete($this->arParams['ENTITY_ID']);
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

        if(!NewSmile\Orm\Helper::isDataManagerClass($this->arParams['DATA_MANAGER_CLASS']))
        {
            ShowError('Entity is not found');
            $isSuccess = false;
        }

        if(!$this->arParams['ENTITY_ID'] && ($this->getMode() !== 'add'))
        {
            ShowError('Entity id is not specified');
            $isSuccess = false;
        }

        return $isSuccess;
    }

    protected function prepareResult()
    {
        $this->arResult['FIELDS'] = $this->getFieldsArray();
        $this->arResult['ACTION'] = $this->getMode();
    }

    protected function getReverseRefName($entityClass, $standardInputName)
    {
        return strtoupper(str_replace('\\', '_', $entityClass)) . ':' . $standardInputName;
    }

    /**
     * Формирует массив полей на основе параметров EDITABLE_FIELDS и SELECT_FIELDS. В массив полей попадают только те поля,
     * которые действительно есть в сущности
     *
     * @return array
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getFieldsArray()
    {
        $selectFields = array_unique($this->arParams['SELECT_FIELDS']);

        foreach ($this->fields as $field)
        {
            if($field instanceof NewSmile\Orm\Fields\ReverseReference)
            {
                $selectFields[] = $field->getName();
            }
        }

        $this->fieldArrayConstructor->setParam('SELECT_FIELDS', $selectFields);

        foreach ($this->fields as $field)
        {
            $fieldName = $field->getName();
            if($fieldName == 'ID') continue;

            $this->fieldArrayConstructor->process($field);
        }

        $this->fieldArrayConstructor->writeValues();
        $fieldArray = $this->fieldArrayConstructor->getResult();

        return $fieldArray;
    }

    /**
     * Возвращает поля сущности, с которой работает компонент. Включая фиктивные поля ReverseReference
     * @return array|ORM\Fields\Field[]
     * @throws \Bitrix\Main\SystemException
     */
    protected function getEntityFields()
    {
        $dataManagerClass = $this->arParams['DATA_MANAGER_CLASS'];

        /**
         * @var ORM\Entity $entity
         */
        $entity = $dataManagerClass::getEntity();
        $fields = $entity->getFields();

        foreach ($this->arParams['REVERSE_REFERENCES'] as $reverseReference)
        {
            $dataManagerClass = $reverseReference['CLASS'];
            $refEntity = $dataManagerClass::getEntity();

            $reverseName = $this->getReverseRefName($dataManagerClass, $reverseReference['KEY_FIELD_NAME']);

            $reverseField =  new NewSmile\Orm\Fields\ReverseReference(
                $reverseName,
                array(
                    'title' => $reverseReference['TITLE'],
                    'source_entity' => $refEntity,
                    'key_field' => $refEntity->getField($reverseReference['KEY_FIELD_NAME'])
                )
            );

            $reverseField->setEntity($entity);
            $fields[] = $reverseField;
        }

        return $fields;
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
     * Подключает блок шаблона из папки page-blocks. Если задан параметр OVERWRITE_TEMPLATE, то будет подключен блок из оригинального шаблона,
     * если он в нем есть. Иначе будет подключен блок из переопределяемого шаблона. Таким образом можно например использовать стандартный шаблон,
     * переопределив лишь один блок в новом шаблоне.
     * @param string $pageBlockCode - код подключаемого блока
     * @param mixed $pageBlockData - информация для вывода в блоке
     */
    public function includePageBlock($pageBlockCode, $pageBlockData)
    {
        if(!$this->doIncludePageBlock($pageBlockCode, $pageBlockData, $this->mainTemplateFolder))
        {
            $this->includeOriginalPageBlock($pageBlockCode, $pageBlockData);
        }
    }

    public function includeOriginalPageBlock($pageBlockCode, $pageBlockData)
    {
        $this->doIncludePageBlock($pageBlockCode, $pageBlockData, $this->originalTemplateFolder);
    }

    protected function doIncludePageBlock($pageBlockCode, $pageBlockData, $templatePath)
    {
        if(!$pageBlockCode) return false;

        $pageBlockRelativePath = '/page-blocks/' . $pageBlockCode . '.php';

        $pageBlockPath = $_SERVER['DOCUMENT_ROOT'] . $templatePath . $pageBlockRelativePath;

        if(file_exists($pageBlockPath))
        {
            $component = $this;
            include $pageBlockPath;
            return true;
        }

        return false;
    }

    /**
     * Подключает шаблон с возможностью перезаписи шаблона, указанного в параметре OVERWRITE_TEMPLATE. Если определенный
     * файл шаблона будет найден в папке шаблона компонента, то будет подключен он. Иначе - будет подключен файл из шаблона в параметре
     * OVERWRITE_TEMPLATE
     */
    public function includeTemplate()
    {
        $this->initComponentTemplate();
        $this->mainTemplateFolder = $this->getTemplate()->GetFolder();

        if($this->arParams['OVERWRITE_TEMPLATE'])
        {
            $this->setTemplateName($this->arParams['OVERWRITE_TEMPLATE']);
            $this->initComponentTemplate();
            $this->originalTemplateFolder = $this->getTemplate()->GetFolder();;
        }

        $this->includeTemplatePage('result_modifier');
        $this->includeTemplatePage('template');
        $this->includeTemplatePage('component_epilog');
        \Bitrix\Main\Page\Asset::getInstance()->addJs($this->mainTemplateFolder . '/script.js');
    }

    public function includeTemplatePage($templatePage)
    {
        $mainTemplatePagePath = $_SERVER['DOCUMENT_ROOT'] . $this->mainTemplateFolder . '/' . $templatePage . '.php';
        $bIncludeOriginal = true;

        if(file_exists($mainTemplatePagePath))
        {
            if($templatePage == 'result_modifier')
            {
                $arResult =& $this->arResult;
            }
            else
            {
                $arResult = $this->arResult;
            }

            $arParams =& $this->arParams;


            include $mainTemplatePagePath;

            $bIncludeOriginal = $templatePage == 'template';
        }

        if($bIncludeOriginal && file_exists($_SERVER['DOCUMENT_ROOT'] . $this->originalTemplateFolder . '/' . $templatePage . '.php'))
        {
            $this->includeComponentTemplate($templatePage);
        }
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
            $this->fields = $this->getEntityFields();
            $this->processRequest($this->request);

            if($this->getMode() != 'delete')
            {
                $this->prepareResult();
            }

            $this->includeTemplate();
        }
	}
}
?>