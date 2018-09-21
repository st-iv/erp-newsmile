<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if($_REQUEST['LOAD_ELEMENTS'] == 'Y')
{
    $APPLICATION->RestartBuffer();
}
$APPLICATION->IncludeComponent(
    "newSmile:entity.list",
    "section",
    Array(
        'DATA_MANAGER_CLASS_ELEMENT' => $arParams['DATA_MANAGER_CLASS_ELEMENT'],
        'DATA_MANAGER_CLASS_GROUP' => $arParams['DATA_MANAGER_CLASS_GROUP'],
        'SECTION_ID' => $arResult['VARIABLES']['SECTION_ID'],
        'SECTION_FIELDS' => $arParams['LIST_SECTION_FIELDS'],
        'ELEMENT_FIELDS' => $arParams['LIST_ELEMENT_FIELDS'],
        'INDEX_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['groups'],
        'SECTION_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['group'],
        'ELEMENT_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['element'],
        'SECTION_EDIT_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['edit_group'],
        'ELEMENT_EDIT_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['edit_element'],
    ),
    $component
);

if($_REQUEST['LOAD_ELEMENTS'] == 'Y')
{
    die();
}
?>

