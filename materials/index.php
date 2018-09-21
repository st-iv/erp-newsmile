<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Материалы");
?>

<?$APPLICATION->IncludeComponent(
    "newSmile:entity",
    "",
    Array(
        'DATA_MANAGER_CLASS_ELEMENT' => 'Mmit\NewSmile\MaterialTable',
        'DATA_MANAGER_CLASS_GROUP' => 'Mmit\NewSmile\MaterialGroupTable',
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/materials/',
        'SECTION_ID' => $arResult['VARIABLES']['SECTION_ID'],
        'LIST_SECTION_FIELDS' => array('NAME'),
        'LIST_ELEMENT_FIELDS' => array('NAME'),
        'EDIT_GROUPS_EDIT_FIELDS' => array('NAME', 'GROUP'),
        'EDIT_GROUPS_SHOW_FIELDS' => array('GROUP.NAME'),
        'EDIT_ELEMENTS_EDIT_FIELDS' => array('NAME', 'GROUP', 'MEASURE'),
        'EDIT_ELEMENTS_SHOW_FIELDS' => array('GROUP.NAME', 'MEASURE.NAME'),
    )
);?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>