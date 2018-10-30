<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Пациенты");

\Mmit\NewSmile\Ajax::start('patient-card');

$APPLICATION->IncludeComponent(
    "newSmile:entity",
    "patient_card",
    Array(
        'DATA_MANAGER_CLASS_ELEMENT' => 'Mmit\NewSmile\PatientCardTable',
        'ELEMENT_VIEW_FIELDS' => ['LAST_NAME', 'NAME', 'SECOND_NAME'],
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/patientcard/',
        'SEF_URL_TEMPLATES' => [
            'group' => '',
            'element' => 'view/#ELEMENT_ID#/'
        ]
    )
);

\Mmit\NewSmile\Ajax::finish();

?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>