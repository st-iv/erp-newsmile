<?php

namespace Mmit\NewSmile\Component;

use Mmit\NewSmile\Ajax;

/**
 * Реализует поддержку ajax областей - компонент не будет выполняться при ajax запросах, если не запрошена его ajax-область.
 * Реализует поддержку параметра PARENT_TEMPLATE. Этот параметр дает возможность использовать отдельные page-block из шаблона, указанного
 * в качестве родительского
 *
 * Class AdvancedComponent
 * @package Mmit\NewSmile\Component
 */
abstract class AdvancedComponent extends \CBitrixComponent
{
    protected $parentTemplateFolder;

    public function canExecute()
    {
        return (!Ajax::isAjaxQuery() || Ajax::isAreaRequested());
    }

    /**
     * Подключает блок шаблона из папки page-blocks. Если задан параметр PARENT_TEMPLATE, то будет подключен блок из родительского шаблона,
     * если он в нем есть. Иначе будет подключен блок из текущего шаблона.
     * @param string $pageBlockCode - код подключаемого блока
     * @param mixed $data - информация для вывода в блоке
     */
    public function includePageBlock($pageBlockCode, $data)
    {
        if(!$this->doIncludePageBlock($pageBlockCode, $data, $this->getTemplate()->GetFolder()))
        {
            $this->includeParentPageBlock($pageBlockCode, $data);
        }
    }

    public function includeParentPageBlock($pageBlockCode, $data)
    {
        if($this->parentTemplateFolder)
        {
            $this->doIncludePageBlock($pageBlockCode, $data, $this->parentTemplateFolder);
        }
    }

    protected function doIncludePageBlock($pageBlockCode, $data, $templatePath)
    {
        if(!$pageBlockCode) return false;

        $pageBlockRelativePath = '/page-blocks/' . $pageBlockCode . '.php';

        $pageBlockPath = $_SERVER['DOCUMENT_ROOT'] . $templatePath . $pageBlockRelativePath;

        if(file_exists($pageBlockPath))
        {
            $component = $this;
            $arParams = $this->arParams;
            include $pageBlockPath;
            return true;
        }

        return false;
    }

    final function onPrepareComponentParams($arParams)
    {
        if($this->canExecute())
        {
            return $this->prepareParams($arParams);
        }
        else
        {
            return $arParams;
        }
    }

    final function executeComponent()
    {
        if(!$this->canExecute()) return null;

        if($this->arParams['PARENT_TEMPLATE'])
        {
            $templateName = $this->getTemplateName();

            /* узнаем путь к родительскому шаблону */
            $this->setTemplateName($this->arParams['PARENT_TEMPLATE']);
            $this->initComponentTemplate();
            $this->parentTemplateFolder = $this->getTemplate()->GetFolder();

            /* возвращаем прежний шаблон */
            $this->setTemplateName($templateName);
        }

        $this->initComponentTemplate();

        if(Ajax::isAjaxQuery())
        {
            $scriptJs = $this->getTemplate()->GetFolder() . '/script.js';

            if(file_exists($_SERVER['DOCUMENT_ROOT'] . $scriptJs))
            {
                Ajax::addJs($scriptJs);
            }
        }

        return $this->execute();
    }

    protected function prepareParams(array $arParams)
    {
        return $arParams;
    }

    abstract protected function execute();
}