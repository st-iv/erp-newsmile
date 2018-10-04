<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новый раздел");
?>

<?$APPLICATION->IncludeComponent(
    "newSmile:entity",
    "elements_only",
    Array(
        'DATA_MANAGER_CLASS_ELEMENT' => 'Mmit\NewSmile\DoctorTable',
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/doctors/',
        'LIST_ELEMENT_FIELDS' => array('NAME'),
        'EDIT_ELEMENTS_EDIT_FIELDS' => array('*'),
        'EDIT_ELEMENTS_SHOW_FIELDS' => array('USER.NAME', 'CLINIC.NAME'),
        'LIST_ELEMENT_NAME_TEMPLATE' => '#LAST_NAME# #NAME# #SECOND_NAME#',
        'LIST_ELEMENT_FIELDS' => array('LAST_NAME', 'NAME', 'SECOND_NAME'),
        'REVERSE_REFERENCES' => array(
            'Mmit\NewSmile\DoctorSpecializationTable:DOCTOR' => array(
                'EDITABLE_FIELDS' => array('SPECIALIZATION'),
                'SINGLE_MODE' => true,
            )
        )
    )
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>