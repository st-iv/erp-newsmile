<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['ELEMENTS'] as &$element)
{
    unset($element['NAME_BY_TEMPLATE']);
}

unset($element);
