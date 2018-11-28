<?php

namespace Mmit\NewSmile\Component;

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Mmit\NewSmile\Ajax;
use Mmit\NewSmile\Application;
use Mmit\NewSmile\Helpers;

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

    /**
     * Проверяет, нужно ли запускать компонент на данном хите (это зависит от того, включен ли компонент в запрашиваемую ajax область)
     * @return bool
     */
    public function canExecute()
    {
        return (!Ajax::isAjaxQuery() || Ajax::isCurrentBlockRequested());
    }

    /**
     * Подключает блок шаблона из папки page-blocks. Если задан параметр PARENT_TEMPLATE, то будет подключен блок из родительского шаблона,
     * если он в нем есть. Иначе будет подключен блок из текущего шаблона.
     *
     * @param string $pageBlockCode - код подключаемого блока
     * @param mixed $data - информация для вывода в блоке
     */
    public function includePageBlock($pageBlockCode, $data)
    {
        if (!$this->doIncludePageBlock($pageBlockCode, $data, $this->getTemplate()->GetFolder()))
        {
            $this->includeParentPageBlock($pageBlockCode, $data);
        }
    }

    /**
     * Подключает блок шаблона из родительского шаблона, указанного в параметре компонента PARENT_TEMPLATE
     *
     * @param $pageBlockCode
     * @param $data
     */
    public function includeParentPageBlock($pageBlockCode, $data)
    {
        if ($this->parentTemplateFolder)
        {
            $this->doIncludePageBlock($pageBlockCode, $data, $this->parentTemplateFolder);
        }
    }

    protected function getReactDir($bDocumentRoot = true)
    {
        return ($bDocumentRoot ? $_SERVER['DOCUMENT_ROOT'] : '') . $this->getTemplate()->GetFolder() . '/react';
    }

    protected function includeReact()
    {
        global $APPLICATION;

        $reactDir = $this->getReactDir(false);
        $reactDirFull = $_SERVER['DOCUMENT_ROOT'] . $reactDir;

        foreach (scandir($reactDirFull) as $fileName)
        {
            if (!is_dir($reactDirFull . '/' . $fileName) && pathinfo($fileName, PATHINFO_EXTENSION) == 'js')
            {
                $buffer = $APPLICATION->GetPageProperty('REACT_COMPONENTS', '');
                $APPLICATION->SetPageProperty('REACT_COMPONENTS', $buffer . '<script type="text/babel" src="' . $reactDir . '/' . $fileName . '"></script>');
                $hasReactComponents = true;
            }
        }

        /*if ($hasReactComponents)
        {
            Asset::getInstance()->addString('<script src="https://unpkg.com/react@16/umd/react.development.js" crossorigin></script>', true, AssetLocation::BEFORE_CSS);
            Asset::getInstance()->addString('<script src="https://unpkg.com/react-dom@16/umd/react-dom.development.js" crossorigin></script>', true, AssetLocation::BEFORE_CSS);
            Asset::getInstance()->addJs('https://unpkg.com/babel-standalone@6/babel.min.js');
        }*/
    }

    public function renderReactComponent(array $props = [], $name = '', $attrs = '')
    {
        if (!$name)
        {
            $name = $this->getName();
            $name = substr($name, strpos($name, ':') + 1);
            $name = str_replace('.', '-', $name) . '-' . $this->getTemplate()->GetName();
        }

        $rootId = 'react-render-' . $name;

        Application::getInstance()->addReactRoot(Helpers::getCamelCase($name), $rootId, $props);
        ?>
        <div id="<?=$rootId?>" <?=$attrs?>></div>
        <?
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

        $this->includeReact();

        return $this->execute();
    }

    /**
     * Подготовка и валидация параметров компонента
     * @param array $arParams
     *
     * @return array
     */
    protected function prepareParams(array $arParams)
    {
        return $arParams;
    }

    /**
     * Выполняет логику компонента
     * @return mixed
     */
    abstract protected function execute();
}