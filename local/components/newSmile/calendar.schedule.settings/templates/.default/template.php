<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<table>
    <tr>
        <td>
            <table border="1">
                <tr>
                    <td>Нечетная</td>
                    <td><a href="?THIS_DATE=1993-04-26">Пн</a></td>
                    <td><a href="?THIS_DATE=1993-04-27">Вт</a></td>
                    <td><a href="?THIS_DATE=1993-04-28">Ср</a></td>
                    <td><a href="?THIS_DATE=1993-04-29">Чт</a></td>
                    <td><a href="?THIS_DATE=1993-04-30">Пт</a></td>
                    <td><a href="?THIS_DATE=1993-05-01">Сб</a></td>
                    <td><a href="?THIS_DATE=1993-05-02">Вс</a></td>
                </tr>
                <tr>
                    <td>Четная</td>
                    <td><a href="?THIS_DATE=1993-05-03">Пн</a></td>
                    <td><a href="?THIS_DATE=1993-05-04">Вт</a></td>
                    <td><a href="?THIS_DATE=1993-05-05">Ср</a></td>
                    <td><a href="?THIS_DATE=1993-05-06">Чт</a></td>
                    <td><a href="?THIS_DATE=1993-05-07">Пт</a></td>
                    <td><a href="?THIS_DATE=1993-05-08">Сб</a></td>
                    <td><a href="?THIS_DATE=1993-05-09">Вс</a></td>
                </tr>
            </table>
        </td>
        <td>
            <div id="calendar-template">
                <table border="1" width="100%">
                <tr>
                    <th>Время</th>
                    <?foreach ($arResult['WORK_CHAIR'] as $arWorkChair):?>
                        <th><?=$arWorkChair['NAME'];?></th>
                    <?endforeach;?>
                </tr>
                <?/*<tr>
        <td></td>
        <?foreach ($arResult['WORK_CHAIR'] as $arWorkChair):?>
        <td>
            <?foreach ($arWorkChair['MAIN_DOCTOR'] as $arDoctor):?>
                <span <?='style="background-color: '.$arResult['MAIN_DOCTOR_ID'][$arDoctor]['COLOR'].';"'?>><?=$arResult['MAIN_DOCTOR_ID'][$arDoctor]['NAME']?></span>
            <?endforeach;?>
        </td>
        <?endforeach;?>
    </tr>*/?>
                <?foreach ($arResult['VISIT'] as $arVisit):?>
                    <?if($arVisit['NAME'] == '09:00' || $arVisit['NAME'] == '15:00'):?>
                        <tr data-start-time="<?=$arResult['THIS_DATE'] . ' ' . $arVisit['NAME']?>">
                            <td></td>
                            <?foreach ($arVisit['WORK_CHAIR'] as $key => $arWorkChair):?>
                                <?
                                $status = '';
                                if ($arResult['SCHEDULE'][$arVisit['NAME']][$key]['PATIENT_ID']) {
                                    $status = 'background-color: red;';
                                } elseif (!empty($arResult['SCHEDULE'][$arVisit['NAME']][$key]['MAIN_DOCTOR_ID'])) {
                                    $status = 'background-color: ' . $arResult['MAIN_DOCTOR_ID'][$arResult['SCHEDULE'][$arVisit['NAME']][$key]['MAIN_DOCTOR_ID']]['COLOR'] . ';';
                                } else {
                                    $status = 'background-color: gray;';
                                }
                                ?>
                                <td class="visit" data-work-chair="<?=$arWorkChair['ID']?>">
                                    <span <?='style="background-color: '.$arResult['MAIN_DOCTOR_ID'][$arResult['SCHEDULE'][$arVisit['NAME']][$key]['MAIN_DOCTOR_ID']]['COLOR'].';"'?>><?=$arResult['MAIN_DOCTOR_ID'][$arResult['SCHEDULE'][$arVisit['NAME']][$key]['MAIN_DOCTOR_ID']]['NAME']?></span>
                                </td>
                            <?endforeach;?>
                        </tr>
                    <?endif;?>
                    <tr data-time-visit="<?=$arResult['THIS_DATE'] . ' ' . $arVisit['NAME']?>">
                        <td><?=$arVisit['NAME'];?></td>
                        <?foreach ($arVisit['WORK_CHAIR'] as $key => $arWorkChair):?>
                            <?
                            $status = '';
                            if ($arResult['SCHEDULE'][$arVisit['NAME']][$key]['PATIENT_ID']) {
                                $status = 'background-color: red;';
                            } elseif (!empty($arResult['SCHEDULE'][$arVisit['NAME']][$key]['DOCTOR_ID'])) {
                                $status = 'background-color: ' . $arResult['DOCTOR_ID'][$arResult['SCHEDULE'][$arVisit['NAME']][$key]['DOCTOR_ID']]['COLOR'] . ';';
                            } else {
                                $status = 'background-color: gray;';
                            }
                            ?>
                            <td width="100px" style="<?=$status;?>" class="visit"
                                data-work-chair="<?=$arWorkChair['ID']?>"
                                data-schedule-id="<?=$arResult['SCHEDULE'][$arVisit['NAME']][$key]['ID'];?>"
                                data-doctor-id="<?=$arResult['SCHEDULE'][$arVisit['NAME']][$key]['DOCTOR_ID']?>">
                                <?if(!empty($arWorkChair['PATIENT_ID'])):?>
                                    <?
                                    $time = $arWorkChair['TIME_END']->getTimestamp() - $arWorkChair['TIME_START']->getTimestamp();
                                    $length = intval($time / 900);
                                    ?>
                                    <div class="visit-block" style="height: <?=($length * 22);?>px">
                                        <?=$arWorkChair['UF_PATIENT_NAME'];?>
                                    </div>
                                <?endif;?>
                                <?if(($arResult['SCHEDULE'][$arVisit['NAME']][$key]['DOCTOR_ID'] != $arResult['SCHEDULE'][$arVisit['NAME']][$key]['MAIN_DOCTOR_ID'])):?>
                                    <small><i><?=$arResult['DOCTOR_ID'][$arResult['SCHEDULE'][$arVisit['NAME']][$key]['DOCTOR_ID']]['NAME']?></i></small>
                                <?endif;?>
                            </td>
                        <?endforeach;?>
                    </tr>
                <?endforeach;?>
            </table>
            </div>
        </td>
    </tr>
</table>
<a href="?ACTION=ADD_SCHEDULE">Создать расписание на месяц</a>
<script>
    BX.ready(function(){
        params = <?=CUtil::PhpToJSObject(array(
            'doctors' => $arResult['DOCTORS']
        ));?>;
        window.scheduleSettings = new window.scheduleSettings(params);
    });
</script>