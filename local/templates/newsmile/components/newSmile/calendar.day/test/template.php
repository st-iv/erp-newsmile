<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<div class="calendar-day" id="calendar-day"></div>

<script>
    var calendarDay = new CalendarDay('#calendar-day', <?=\CUtil::PhpToJSObject($arResult['JS_PARAMS'])?>);
</script>
