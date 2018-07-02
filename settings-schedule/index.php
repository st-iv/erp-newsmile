<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Главная страница");
?>
<?$APPLICATION->IncludeComponent(
    "newSmile:calendar.schedule.settings",
    "",
    Array(

    )
);?>
<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>