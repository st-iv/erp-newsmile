<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Прейскурант");
?>

<?$APPLICATION->IncludeComponent(
    "newSmile:entity",
    "",
    Array(
        'DATA_MANAGER_CLASS_ELEMENT' => 'Mmit\NewSmile\Service\ServiceTable',
        'DATA_MANAGER_CLASS_GROUP' => 'Mmit\NewSmile\Service\GroupTable',
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/price-list/',
        'SECTION_ID' => $arResult['VARIABLES']['SECTION_ID'],
        'LIST_SECTION_FIELDS' => array('NAME'),
        'LIST_ELEMENT_FIELDS' => array('NAME'),
        'EDIT_GROUPS_EDIT_FIELDS' => array('NAME', 'GROUP'),
        'EDIT_GROUPS_SHOW_FIELDS' => array('GROUP.NAME'),
        'EDIT_ELEMENTS_EDIT_FIELDS' => array('NAME', 'GROUP', 'MEASURE'),
        'EDIT_ELEMENTS_SHOW_FIELDS' => array('GROUP.NAME'),
        'REVERSE_REFERENCES_ELEMENT' => array(
            'Mmit\NewSmile\Service\PriceTable:SERVICE' => array(
                'EDITABLE_FIELDS' => array('PRICE', 'MIN_PRICE', 'MAX_PRICE'),
                'TITLE' => 'Цена',
                'SINGLE_MODE' => true,
                'PRESET' => array(
                    'CLINIC_ID' => 1
                )
            )
        )
    )
);?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>