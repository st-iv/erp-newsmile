<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
use Mmit\NewSmile;

$rowHeight = 22;
$rowMargin = 2;
?>

<div class="main_content_center">
    <div id="day-calendar">
        <div class="dayCalendar_cont">
            <div class="dayCalendar_header">
                <span><?=NewSmile\Date\Helper::date('l_ru - d F_ru_gen', strtotime($arResult['THIS_DATE']))?></span>
            </div>

            <div class="dayCalendar_body">

                <?foreach ($arResult['WORK_CHAIR'] as $workChair):?>
                    <div class="dayCalendar_column">

                        <div class="dayCalendar_roomName">
                            <?=$workChair['NAME']?>
                        </div>

                        <?foreach ($workChair['MAIN_DOCTORS'] as $mainDoctor):?>
                            <?if($mainDoctor['ID']):?>
                                <div class="dayCalendar_doctor" style="background-color: <?=$mainDoctor['COLOR']?>;">
                                    <?=$mainDoctor['FIO']?>
                                </div>
                            <?else:?>
                                <div class="dayCalendar_doctor emptyD"></div>
                            <?endif;?>
                        <?endforeach;?>

                        <?foreach ($workChair['INTERVALS'] as $interval):?>
                            <?
                            $height = ($interval['ROWS_COUNT'] * $rowHeight + ($interval['ROWS_COUNT'] - 1) * $rowMargin);
                            $doctorId = ($interval['UF_DOCTOR_ID'] ?: $interval['UF_MAIN_DOCTOR_ID']);
                            $isMainDoctor = $interval['UF_MAIN_DOCTOR_ID'] && (empty($interval['UF_DOCTOR_ID']) || ($interval['UF_MAIN_DOCTOR_ID'] == $interval['UF_DOCTOR_ID']));

                            $class = 'dayCalendar_interval';
                            $class .= ((!$interval['IS_VISIT'] && !$doctorId) ? ' emptyI' : '');
                            $class .= ((!$interval['IS_VISIT'] && $doctorId) ? ' resrvdI' : '');
                            ?>

                            <div class="<?=$class?>" style="height: <?=$height?>px;" data-doctor-id="<?=$doctorId?>"
                                 data-is-main-doctor="<?=$isMainDoctor?>" data-is-visit="<?=$interval['IS_VISIT']?>">
                                <?if($interval['IS_VISIT']):?>
                                    <span><?=$interval['UF_PATIENT_FIO']?></span>
                                <?endif;?>

                                <?if($doctorId && !$isMainDoctor):?>
                                    <span class="freedoctor_intrvl">Врач - <?=$interval['UF_DOCTOR_FIO']?></span>
                                <?endif;?>

                                <div class="dayCalendar_popup">
                                    <?/*---------------------------- POPUP меню --------------------------------*/?>
                                    <?include 'page_blocks/interval_menu.php'?>

                                    <?/*--------------------- POPUP ИНФОМРАЦИЯ о приеме ------------------------*/?>
                                    <?include 'page_blocks/interval_popup.php'?>

                                    <div class="dClndr_parrow"></div>
                                </div>
                            </div>
                        <?endforeach;?>
                    </div>
                <?endforeach;?>

                <?foreach(array('leftTl', 'rightTl') as $tlClass):?>
                    <div class="dayCalendar_<?=$tlClass?>">
                        <?foreach ($arResult['TIME_LINE'] as $timeItem):?>
                            <?
                            $time = $timeItem['TIME'];
                            $height = ($timeItem['ROWS_COUNT'] * $rowHeight + ($timeItem['ROWS_COUNT'] - 1) * $rowMargin);
                            ?>
                            <div class="dayCalendar_timeItem" style="height: <?=$height?>px;"><?=$time->format('H:i');?></div>
                        <?endforeach;?>
                    </div>
                <?endforeach;?>

            </div>
        </div>
    </div>
</div>

<?
$jsParams = array(
    'doctors' => $arResult['DOCTORS'],
    'selector' => '#day-calendar'
);
?>

<script>
    var calendarDay = new CalendarDay(<?=\CUtil::PhpToJSObject($jsParams)?>);
</script>