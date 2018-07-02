<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Главная страница");
?>
<table>
    <tr>
        <td>
            <?$APPLICATION->IncludeComponent(
                "newSmile:calendar",
                "",
                Array(

                )
            );?>
        </td>
        <td rowspan="3">
            <?$APPLICATION->IncludeComponent(
                "newSmile:calendar.day",
                "",
                Array(

                )
            );?>
        </td>
    </tr>
    <tr>
        <td>
            <?$APPLICATION->IncludeComponent(
                "newSmile:callpatient",
                "",
                Array(

                )
            );?>
        </td>
    </tr>
    <tr>
        <td>
            <?$APPLICATION->IncludeComponent(
                "newSmile:visit.add",
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