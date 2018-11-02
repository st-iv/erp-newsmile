<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
use Mmit\NewSmile\Ajax;


Ajax::start('patient-file-edit');?>

<?$APPLICATION->IncludeComponent(
    "newSmile:entity.edit",
    "patient-file",
    Array(
        'DATA_MANAGER_CLASS' => '\\Mmit\\NewSmile\\FileTable',
        'EDITABLE_FIELDS' => ['FILE', 'TYPE', 'NAME', 'DATE_CREATE', 'COMMENT', 'TEETH'],
        'ENTITY_ID' => $_REQUEST['FILE_ID'],
        'PARENT_TEMPLATE' => '.default',
        'PRESET' => [
            'PATIENT_ID' => [
                'VALUE' => $_REQUEST['PATIENT_ID'],
                'HIDDEN' => true
            ]
        ]
    )
);
?>

<?Ajax::finish();

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_after.php");