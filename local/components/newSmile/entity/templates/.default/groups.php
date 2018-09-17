<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$APPLICATION->IncludeComponent(
    "newSmile:entity.list",
    "",
    Array(
        'ENTITY_CLASS_GROUP' => $arParams['ENTITY_CLASS_GROUP'],
        'SECTION_FIELDS' => $arParams['LIST_SECTION_FIELDS'],
        'INDEX_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['groups'],
        'SECTION_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['group'],
        'DETAIL_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['element'],
        'SECTION_EDIT_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['edit_group'],
    ),
    $component
);?>