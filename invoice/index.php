<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новый раздел");
?>

<?$APPLICATION->IncludeComponent(
        'newSmile:visit.invoice.create',
        []
)?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>