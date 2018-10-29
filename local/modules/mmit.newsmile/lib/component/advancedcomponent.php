<?php

namespace Mmit\NewSmile\Component;

use Mmit\NewSmile\Ajax;

/**
 * Реализует поддержку ajax областей - компонент не будет выполняться при ajax запросах, если не запрошена его ajax-область.
 *
 * Class AdvancedComponent
 * @package Mmit\NewSmile\Component
 */
abstract class AdvancedComponent extends \CBitrixComponent
{
    public function canExecute()
    {
        return (!Ajax::isAjaxQuery() || Ajax::isAreaRequested());
    }

    public function onPrepareComponentParams($arParams)
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

    public function executeComponent()
    {
        if($this->canExecute())
        {
            return $this->execute();
        }

        return null;
    }

    abstract protected function prepareParams(array $arParams);
    abstract protected function execute();
}