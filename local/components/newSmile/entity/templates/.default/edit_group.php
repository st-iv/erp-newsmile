<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if($_REQUEST['ajax'] == 'Y')
{
    $APPLICATION->RestartBuffer();
}

$APPLICATION->IncludeComponent(
    "newSmile:entity.edit",
    "",
    Array(
        'ENTITY_CLASS' => $arParams['ENTITY_CLASS_GROUP'],
        'EDIT_FIELDS' => $arParams['EDIT_GROUPS_EDIT_FIELDS'],
        'SHOW_FIELDS' => $arParams['EDIT_GROUPS_SHOW_FIELDS'],
        'ENTITY_ID' => $arResult['VARIABLES']['SECTION_ID'],
        'ADD_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['add_group'],
    ),
    $component
);


if($_REQUEST['ajax'] == 'Y')
{
    die();
}

?>