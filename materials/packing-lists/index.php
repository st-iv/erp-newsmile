<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Накладные");
?>

<?$APPLICATION->IncludeComponent(
    "newSmile:entity",
    "elements_only",
    Array(
        'DATA_MANAGER_CLASS_ELEMENT' => 'Mmit\NewSmile\PackingListTable',
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/materials/packing-lists/',
        'LIST_ELEMENT_FIELDS' => array('ID', 'DATE'),
        'LIST_ELEMENT_NAME_TEMPLATE' => 'Накладная №#ID# от #DATE#',
        'EDIT_ELEMENTS_EDIT_FIELDS' => array('DATE', 'RECEIVING_DATE', 'STORE', 'Mmit\NewSmile\PackingListItem'),
        'EDIT_ELEMENTS_SHOW_FIELDS' => array('STORE.NAME'),
        'REVERSE_REFERENCES' => array(
            'Mmit\NewSmile\PackingListItemTable:PACKING_LIST' => array(
                'EDITABLE_FIELDS' => array('QUANTITY', 'PRICE', 'MATERIAL'),
                'SELECT_FIELDS' => array('MATERIAL.NAME'),
                'TITLE' => 'Материалы'
            )
        )
    )
);?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>