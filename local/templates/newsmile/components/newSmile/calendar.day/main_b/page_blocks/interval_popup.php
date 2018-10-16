<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
/**
 * @var array $interval
 */

use Mmit\NewSmile;
?>

<?if($interval['IS_VISIT']):?>
    <?$visitDuration = $interval['TIME_END']->getTimestamp() - $interval['TIME_START']->getTimestamp();?>

    <div class="dClndr_popup_card">
        <div class="dClndr_popup_info">
            <div class="dClndr_pinfo_name">
                <div>
                    <span><?=$interval['UF_PATIENT_LAST_NAME'] . ' ' . $interval['UF_PATIENT_NAME'] . ' ' . $interval['UF_PATIENT_SECOND_NAME']?></span>
                    - <?=$interval['UF_PATIENT_AGE']?>
                </div>
            </div>

            <?if($interval['UF_PATIENT_NUMBER']):?>
                <div class="dClndr_pinfo_number">
                    <div>Карта <?=$interval['UF_PATIENT_NUMBER']?></div>
                    <span></span>
                </div>
            <?endif;?>

            <div class="dClndr_pinfo_phone">
                <div><?=$interval['UF_PATIENT_PERSONAL_PHONE']?></div>
            </div>

            <div class="dClndr_pinfo_time">
                <span><?=$interval['TIME_START']->format('H:i')?> - <?=$interval['TIME_END']->format('H:i')?></span>
                <span><?=NewSmile\Date\Helper::formatTimeInterval($visitDuration)?></span>
            </div>
        </div>
    </div>
<?endif;?>
