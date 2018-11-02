<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Пациенты");

\Mmit\NewSmile\Ajax::start('patient-card');

$APPLICATION->IncludeComponent(
    "newSmile:entity",
    "patient_card",
    Array(
        'DATA_MANAGER_CLASS_ELEMENT' => 'Mmit\NewSmile\PatientCardTable',
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/patientcard/',
        'SEF_URL_TEMPLATES' => [
            'group' => '',
            'edit_group' => '',
            'element' => 'view/#ELEMENT_ID#/',
            'edit_element' => 'edit/#ELEMENT_ID#/'
        ],
        'ELEMENT_VIEW_FIELDS' => ['NUMBER', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'PERSONAL_BIRTHDAY', 'RESIDENTIAL_ADDRESS', 'PERSONAL_PHONE', 'COMMENT'],
        'ELEMENT_FIELD_GROUPS' => [
            [
                'TITLE' => 'Общая информация',
                'FIELDS' => ['NUMBER', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'PERSONAL_BIRTHDAY', 'RESIDENTIAL_ADDRESS', 'PERSONAL_PHONE'],
            ],
            [
                'TITLE' => 'Лечение',
                'FIELDS' => ['FIRST_LAST_VISIT', 'ATTENDING_DOCTORS'],
            ],
            [
                'TITLE' => 'Документы',
                'FIELDS' => ['PASSPORT', 'POLICIES'],
            ],
            [
                'TITLE' => 'Дополнительная информация',
                'FIELDS' => ['LEARN_FROM', 'DISCOUNTS', 'PERSONAL_ACCOUNT', 'FAMILY_ACCOUNT', 'TREATMENT_PRICE', 'FAMILY', 'COMMENT'],
            ]
        ],
    )
);

\Mmit\NewSmile\Ajax::finish();

?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>