<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$APPLICATION->IncludeComponent(
    "newSmile:entity.list",
    "elements",
    Array(
        'DATA_MANAGER_CLASS_ELEMENT' => $arParams['DATA_MANAGER_CLASS_ELEMENT'],
        'ELEMENT_FIELDS' => $arParams['LIST_ELEMENT_FIELDS'],
        'ELEMENT_NAME_TEMPLATE' => $arParams['LIST_ELEMENT_NAME_TEMPLATE'],
        'INDEX_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['groups'],
        'DETAIL_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['element'],
        'ELEMENT_EDIT_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['edit_element'],
    ),
    $component
);?>