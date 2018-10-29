<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Главная страница");
?>
<?
$APPLICATION->IncludeComponent(
    "newSmile:entity.edit",
    "waiting_list",
    Array(
        'DATA_MANAGER_CLASS' => 'Mmit\\NewSmile\\WaitingListTable',
        'EDITABLE_FIELDS' => ['*'],
        'SELECT_FIELDS' => ['*', 'DOCTOR.LAST_NAME', 'PATIENT.LAST_NAME', 'CLINIC.NAME'],
        'OVERWRITE_TEMPLATE' => '.default'
    ),
    $component
);
?>

<?
$APPLICATION->IncludeComponent(
    "newSmile:entity.list",
    "just_table",
    Array(
        'DATA_MANAGER_CLASS_ELEMENT' => 'Mmit\\NewSmile\\WaitingListTable',
        'ELEMENT_FIELDS' => ['*', 'DOCTOR.LAST_NAME', 'PATIENT.LAST_NAME', 'CLINIC.NAME'],
    ),
    $component
);
?>

<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>