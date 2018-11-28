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
    ".default____b",
    array(
        'COLORS' => array(
            0 => array(
                'BACKGROUND' => '454545',
                'TEXT' => 'fff'
            ),
            30 => array(
                'BACKGROUND' => 'ff3758',
                'TEXT' => 'fff'
            ),
            90 => array(
                'BACKGROUND' => 'ffb637',
                'TEXT' => 'fff'
            ),
            150 => array(
                'BACKGROUND' => 'eaed14',
            ),
            270 => array(
                'BACKGROUND' => '73cc00',
                'TEXT' => 'fff'
            )
        ),
        'FILTER' => $filter,
        'CALENDAR_DAY_AJAX_AREA' => 'calendar-day'
    )
);

NewSmile\Ajax::start('calendar-day');

$APPLICATION->IncludeComponent(
    "newSmile:calendar.day",
    "main",
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