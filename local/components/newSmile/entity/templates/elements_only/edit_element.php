<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if($_REQUEST['ajax'] == 'Y')
{
    $APPLICATION->RestartBuffer();
}

$APPLICATION->IncludeComponent(
    "newSmile:entity.edit",
    "",
    Array(
        'ENTITY_CLASS' => $arParams['ENTITY_CLASS_ELEMENT'],
        'EDIT_FIELDS' => $arParams['EDIT_ELEMENTS_EDIT_FIELDS'],
        'SHOW_FIELDS' => $arParams['EDIT_ELEMENTS_SHOW_FIELDS'],
        'ENTITY_ID' => $arResult['VARIABLES']['ELEMENT_ID'],
        'ADD_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['add_element'],
        'REVERSE_REFERENCES' => $arParams['REVERSE_REFERENCES']
    ),
    $component
);


if($_REQUEST['ajax'] == 'Y')
{
    die();
}

?>