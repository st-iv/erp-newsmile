<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новый раздел");
?>

<?$APPLICATION->IncludeComponent(
    "newSmile:doctor",
    "",
    Array(
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/doctors/',
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