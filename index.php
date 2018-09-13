<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Главная страница");
?>
<table>
    <tr>
        <td colspan="2">
            <?$APPLICATION->IncludeComponent(
                "newSmile:calendar.filter",
                "",
                [
                    'FILTER_NAME' => 'arrFilterCalendar'
                ]
            );?>
        </td>
    </tr>
    <tr>
        <td>
            <?$APPLICATION->IncludeComponent(
                "newSmile:calendar",
                "",
                [
                    'FILTER_NAME' => 'arrFilterCalendar'
                ]
            );?>
        </td>
        <td rowspan="3">
            <div class="calendar-days">
                <?$APPLICATION->IncludeComponent(
                    "newSmile:calendar.day",
                    "",
                    [
                        'FILTER_NAME' => 'arrFilterCalendar'
                    ]
                );?>
            </div>
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