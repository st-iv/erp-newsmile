<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
use Bitrix\Main\Config\Option;
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<div id="calendar-day">
    <div class="calendar-bottom">
        <div class="calendar-bottom-header">
            <div class="calendar-bottom-time"></div>
            <?foreach ($arResult['WORK_CHAIR'] as $arWorkChair):?>
                <div class="calendar-bottom-work_chair"
                     data-work-chair="<?=$arWorkChair['ID'];?>">
                    <?foreach ($arWorkChair['MAIN_DOCTORS'] as $arDoctor):?>
                        <div class="calendar-bottom-item active"
                             data-start-time="<?=$arDoctor['TIME'];?>">
                            <?=$arDoctor['NAME']?>
                        </div>
                    <?endforeach;?>
                </div>
            <?endforeach;?>
        </div>
        <div class="calendar-bottom-body">
            <div class="calendar-bottom-time">
                <?
                $arTimeStart = explode(':', Option::get('mmit.newsmile', "start_time_schedule", '00:00'));
                $arTimeEnd = explode(':', Option::get('mmit.newsmile', "end_time_schedule", '00:00'));

                $timeStart = mktime($arTimeStart[0],$arTimeStart[1],0,0,0,0);
                $timeEnd = mktime($arTimeEnd[0],$arTimeEnd[1],0,0,0,0);
                ?>
                <?while ($timeStart < $timeEnd):?>
                    <div class="calendar-bottom-item"><?=date('H:i',$timeStart );?></div>
                    <?$timeStart += 900;?>
                <?endwhile;?>
            </div>
            <?foreach ($arResult['WORK_CHAIR'] as $arWorkChair):?>
                <div class="calendar-bottom-work_chair"
                     data-work-chair="<?=$arWorkChair['ID'];?>">
                    <?foreach ($arWorkChair['SCHEDULES'] as $arSchedule):?>
                        <?
                        $background = (!empty($arSchedule['UF_DOCTOR_COLOR']))? 'background-color: ' . $arSchedule['UF_DOCTOR_COLOR'] . ';' : '';
                        ?>
                        <div class="calendar-bottom-item <?if(!$arSchedule['PATIENT_ID']):?>active<?endif;?>"
                             data-schedule-id="<?=$arSchedule['ID']?>"
                             data-schedule-time="<?=$arSchedule['TIME']->format('Y-m-d H:i')?>"
                             data-doctor-id="<?=$arSchedule['UF_DOCTOR_ID']?>"
                             style="<?=$background?>">
                            <?if($arSchedule['UF_DOCTOR_ID'] != $arSchedule['UF_MAIN_DOCTOR_ID']):?>
                                <small><i><?=$arSchedule['UF_DOCTOR_NAME'];?></i></small>
                            <?endif;?>
                        </div>
                    <?endforeach;?>
                </div>
            <?endforeach;?>
        </div>
    </div>
    <div class="calendar-top">
        <div class="calendar-top-header">
            <div class="calendar-top-time"></div>
        </div>
        <div class="calendar-top-body">
            <div class="calendar-top-time"></div>
            <?foreach ($arResult['WORK_CHAIR'] as $arWorkChair):?>
                <div class="calendar-top-work_chair">
                    <?foreach ($arWorkChair['VISITS'] as $arVisit):?>
                        <?
                        $background = (!empty($arVisit['UF_DOCTOR_COLOR']))? 'background-color: ' . $arVisit['UF_DOCTOR_COLOR'] . ';' : '';
                        $height = $arVisit['TIME_END']->getTimestamp() - $arVisit['TIME_START']->getTimestamp();
                        $height = intval($height / 900);
                        $height = 'height: ' . ($height * 25 + ($height - 1) * 4) . 'px;';
                        ?>
                        <?if($arVisit['ID']):?>
                            <div class="calendar-top-item calendar-visit"
                                 style="<?=$background?> <?=$height?>"
                                 data-visit-id="<?=$arVisit['ID']?>">
                                <?=$arVisit['UF_PATIENT_NAME']?><br>
                                <?=$arVisit['STATUS_NAME']?>
                            </div>
                        <?else:?>
                            <div class="calendar-top-item calendar-visit"
                                 style="<?=$background?> <?=$height?> width: 1px;">
                            </div>
                        <?endif;?>
                    <?endforeach;?>
                </div>
            <?endforeach;?>
        </div>
    </div>
</div>

<script>
    BX.ready(function(){
        params = <?=CUtil::PhpToJSObject(array(
            'doctors' => $arResult['DOCTORS'],
            'patients' => $arResult['PATIENTS'],
        ));?>;
        new window.calendarDay(params);
    });
</script>