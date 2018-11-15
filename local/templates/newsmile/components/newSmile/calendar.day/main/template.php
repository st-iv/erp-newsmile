<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/day_calendar.js');
?>

<div class="main_content_center">
    <div id="day-calendar"></div>
</div>

<script>
    var calendarDay = new CalendarDay(<?=\CUtil::PhpToJSObject($arResult['JS_PARAMS'])?>, '#day-calendar');
</script>
