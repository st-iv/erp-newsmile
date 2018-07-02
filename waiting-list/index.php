<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Главная страница");
?>
<table>
    <tr>
        <td>
            <?$APPLICATION->IncludeComponent(
                "newSmile:waitinglist.create",
                "",
                Array(

                )
            );?>
        </td>
    </tr>
    <tr>
        <td>
            <?$APPLICATION->IncludeComponent(
                "newSmile:waitinglist.list",
                "",
                Array(

                )
            );?>
        </td>
    </tr>
</table>
<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>