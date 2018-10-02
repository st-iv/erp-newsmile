<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>


    <div class="main_content_left">
        <div class="left_calendar_cont">
            <div id="left-calendar">
            </div>
        </div>
    </div>


<?
$jsParams = array(
    'colors' => $arParams['COLORS'],
    'dateInfo' => $arResult['DATE'],
    'startDay' => $arResult['START_DAY'],
    'endDay' => $arResult['END_DAY'],
    'curDay' => date('Y-m-d'),
    'ajaxUrl' => POST_FORM_ACTION_URI,
    'calendarDayAjaxArea' => $arParams['CALENDAR_DAY_AJAX_AREA']
);

if(\Mmit\NewSmile\Ajax::isAreaRequested())
{
    \Mmit\NewSmile\Ajax::setAreaParam('dateInfo', $arResult['DATE']);
    \Mmit\NewSmile\Ajax::setAreaParam('startDay', $arResult['START_DAY']);
    \Mmit\NewSmile\Ajax::setAreaParam('endDay', $arResult['END_DAY']);
}
?>

<script>
    var calendar = new Calendar(<?=CUtil::PhpToJSObject($jsParams)?>, calendarFilter);
</script>