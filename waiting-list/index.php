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
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>