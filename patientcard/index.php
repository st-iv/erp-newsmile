<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Пациенты");
?>

<?$APPLICATION->IncludeComponent(
    "newSmile:patient.card",
    "",
    Array(
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/patientcard/',
        'SEF_URL_TEMPLATES' => array(
            'index' => 'index.php',
            'view' => 'view/#ELEMENT_ID#/',
            'edit' => 'edit/#ELEMENT_ID#/',
        ),
        'VARIABLE_ALIASES' => array(
            'ACTION',
            'ELEMENT_ID'
        ),
    )
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>