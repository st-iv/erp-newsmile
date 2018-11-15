<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
use Mmit\NewSmile\Ajax;
?>
<div class="header_notif">
    <div class="notif_bell">
        <div class="notif_amnt"><?=$arResult['UNREAD_COUNT']?></div>
    </div>
</div>

<div class="notif_content">
    <div class="notif_header">Уведомления</div>
    <div class="notif_content_close"></div>
    <div class="notif_tabs">
        <div class="notif_tab tActive" data-select="all">Все</div>

        <?foreach($arResult['GROUPS'] as $groupCode => $groupName):?>
            <div class="notif_tab" data-select="<?=$groupCode?>"><?=$groupName?></div>
        <?endforeach;?>

    </div>

    <?Ajax::start('notices-list', true, false);?>

    <div class="notif_items" <?=Ajax::getAreaCodeAttr()?>>

        <?foreach ($arResult['NOTICES'] as $notice):?>
            <div class="notif_item" data-type="<?=$notice['GROUP']?>" data-id="<?=$notice['ID']?>" data-is-read="<?=$notice['IS_READ']?>">
                <div class="notif_data">
                    <div class="notif_data_date"><?=$notice['TIME_FORMATTED']?></div>

                    <?if($notice['TYPE'] == 'VISIT_FINISHED'):?>
                        <div class="notif_data_action">Рассчитать пациента</div>
                    <?elseif($notice['PARAMS']['LINK']):?>
                        <a class="notif_data_action" href="<?=$notice['PARAMS']['LINK']?>">Подробнее</a>
                    <?endif;?>
                </div>
                <div class="notif_status status<?=$notice['TITLE_CLASS']?>"><?=$notice['TITLE']?></div>

                <?if($notice['TYPE'] == 'VISIT_FINISHED'):?>
                    <div class="notif_user">
                        <div class="notif_user_name">
                            <?=$notice['PARAMS']['PATIENT_LAST_NAME'] . ' ' . $notice['PARAMS']['PATIENT_NAME']?><br/>
                            <?=$notice['PARAMS']['PATIENT_SECOND_NAME']?> – <?=$notice['PARAMS']['PATIENT_AGE']?>
                        </div>
                        <div class="notif_user_doctor" style="background-color: <?=$notice['PARAMS']['DOCTOR_COLOR']?>;">
                            <?=$notice['PARAMS']['DOCTOR_FIO']?>
                        </div>
                    </div>
                <?endif;?>


                <div class="notif_text">
                    <?=$notice['TEXT']?>

                    <?if($notice['TYPE'] == 'WAITING_LIST_SUGGEST'):?>
                        <br><br>
                        <?
                        foreach ($notice['PARAMS']['FREE_TIME'] as $date => $freeIntervals):?>
                            На <?=$date?>:
                            <?foreach ($freeIntervals as $interval):?>
                                с <?=$interval['START_TIME']?> до <?=$interval['END_TIME']?> (кресло <?=$interval['WORK_CHAIR']?>);
                            <?endforeach;?>
                            <br>
                        <?endforeach;?>
                    <?endif;?>
                </div>



                <div class="notif_close"></div>
            </div>
        <?endforeach;?>

    </div>

    <?Ajax::finish();?>
</div>

<script>
    var noticeList = new NoticeList();
</script>