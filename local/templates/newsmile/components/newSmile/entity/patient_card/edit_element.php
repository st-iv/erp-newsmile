<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Mmit\NewSmile;

NewSmile\Ajax::start('patient-card');
NewSmile\Ajax::start('patient-main-info');

$APPLICATION->IncludeComponent(
    "newSmile:entity.edit",
    "patient_main_info",
    Array(
        'DATA_MANAGER_CLASS' => $arParams['DATA_MANAGER_CLASS_ELEMENT'],
        'EDITABLE_FIELDS' => $arParams['ELEMENT_VIEW_FIELDS'],
        'ENTITY_ID' => $arResult['VARIABLES']['ELEMENT_ID'],
        'ADD_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['add_element'],
        'REVERSE_REFERENCES' => $arParams['REVERSE_REFERENCES'],
        'PARENT_TEMPLATE' => '.default',
        'GROUPS' => $arParams['ELEMENT_FIELD_GROUPS']
    ),
    $component
);

NewSmile\Ajax::finish();
NewSmile\Ajax::finish();
