<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$APPLICATION->IncludeComponent(
    "newSmile:entity.list",
    "",
    Array(
        'DATA_MANAGER_CLASS_GROUP' => $arParams['DATA_MANAGER_CLASS_GROUP'],
        'GROUP_FIELDS' => $arParams['LIST_SECTION_FIELDS'],
        'INDEX_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['groups'],
        'SECTION_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['group'],
        'DETAIL_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['element'],
        'SECTION_EDIT_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['edit_group'],
    ),
    $component
);?>