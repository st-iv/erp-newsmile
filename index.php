<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Главная страница");

use Mmit\NewSmile;
?>
<?
\Bitrix\Main\Loader::includeModule('mmit.newsmile');

$filter = $APPLICATION->IncludeComponent(
    "newSmile:calendar.filter",
    "",
    array()
);

NewSmile\Ajax::start('calendar', false);
?>
<div class="row main_content">
<?

$APPLICATION->IncludeComponent(
    "newSmile:calendar",
    "",
    array(
        'COLORS' => array(
            0 => 'ffb637',
            50 => 'd2d512',
        ),
        'FILTER' => $filter,
        'CALENDAR_DAY_AJAX_AREA' => 'calendar-day'
    )
);

NewSmile\Ajax::start('calendar-day');

$APPLICATION->IncludeComponent(
    "newSmile:calendar.day",
    "",
    [
        'FILTER' => $filter
    ]
);

NewSmile\Ajax::finish();
?>
</div>
<?
NewSmile\Ajax::finish();


require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>