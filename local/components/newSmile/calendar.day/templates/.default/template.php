<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<div id="calendar-day">
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
                    if ($arResult['SCHEDULE'][$arVisit['NAME']][$key]['ENGAGED'] == 'Y') {
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
                    if ($arResult['SCHEDULE'][$arVisit['NAME']][$key]['ENGAGED'] == 'Y') {
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