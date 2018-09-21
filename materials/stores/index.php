<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Склады");
?>

<?$APPLICATION->IncludeComponent(
    "newSmile:entity",
    "elements_only",
    Array(
        'DATA_MANAGER_CLASS_ELEMENT' => 'Mmit\NewSmile\StoreTable',
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/materials/stores/',
        'LIST_ELEMENT_FIELDS' => array('NAME'),
    )
);?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>