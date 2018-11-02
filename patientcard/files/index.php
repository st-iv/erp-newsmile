<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Пациенты");

\Mmit\NewSmile\Ajax::start('patient-card');

$APPLICATION->IncludeComponent(
    "newSmile:entity",
    "patient_card_files",
    Array(
        'DATA_MANAGER_CLASS_ELEMENT' => 'Mmit\NewSmile\FileTable',
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/patientcard/file/',
        'SEF_URL_TEMPLATES' => [
            'group' => '',
            'edit_group' => '',
            'element' => 'view/#ELEMENT_ID#/',
            'edit_element' => 'edit/#ELEMENT_ID#/'
        ],
        //'ELEMENT_VIEW_FIELDS' => ['NUMBER', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'PERSONAL_BIRTHDAY', 'RESIDENTIAL_ADDRESS', 'PERSONAL_PHONE', 'COMMENT'],

    )
);

\Mmit\NewSmile\Ajax::finish();

?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>