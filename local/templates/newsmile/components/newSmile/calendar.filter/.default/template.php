<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<form action="<?=POST_FORM_ACTION_URI?>" data-ajax-area="calendar"
      id="main-calendar-filter">
    <div class="row shld_filter">
        <div class="shld_filter_title">Расписание</div>

            <select name="SPEC" id="speс">
                <option selected value="">Профессия</option>

                <?foreach ($arResult['SPECIALIZATIONS'] as $specCode => $spec):?>
                    <option value="<?=$specCode?>"><?=$spec?></option>
                <?endforeach;?>
            </select>

            <select name="DOCTOR" id="doctor">
                <option  data-color="fff" disabled selected value="">Врач</option>
                <option  data-color="fff" value="">Любой</option>

                <?foreach ($arResult['DOCTORS'] as $doctor):?>
                    <option data-color="<?=$doctor['COLOR']?>" value="<?=$doctor['ID']?>"><?=$doctor['FIO']?></option>
                <?endforeach;?>

            </select>

            <div class="time-range_cont">
                <div id="time-range"></div>
                <div id="time-range_from">c <span></span></div>
                <div id="time-range_to">до <span></span></div>
            </div>

            <input type="hidden" name="TIME_FROM" id="time-range-from-input">
            <input type="hidden" name="TIME_TO" id="time-range-to-input">
            <input type="hidden" name="THIS_DATE" value="">
            <input type="hidden" name="DATE_FROM" value="">
            <input type="hidden" name="DATE_TO" value="">

            <input class="shld_btn_dcl" type="reset" value="" style="display: none;">
            <input class="shld_btn_acc" type="submit" value="" style="display: none;">
    </div>
</form>

<?
$jsParams = array(
    'startTime' => $arResult['START_TIME'],
    'endTime' => $arResult['END_TIME'],
);
?>

<script>
    var calendarFilter = new CalendarFilter(<?=CUtil::PhpToJSObject($jsParams)?>);
</script>