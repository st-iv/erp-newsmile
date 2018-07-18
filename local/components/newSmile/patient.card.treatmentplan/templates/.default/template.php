<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 04.06.2018
 * Time: 15:02
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

?>
<form action="" class="add-treatment-plan" method="post">
    <input type="hidden" name="CREATE_PLAN" value="Y">
    <input type="text" name="NAME">
    <input type="submit" value="Добавить">
</form>
<?$APPLICATION->IncludeComponent(
    "newSmile:tooth.list",
    "",
    Array(
    )
);?>
<?foreach ($arResult['TREATMENT_PLAN'] as $arPlan):?>
    <table border="1" cellspacing="0" cellpadding="2" class="treatment-plan" data-plan-id="<?=$arPlan['ID']?>">
        <tr>
            <td><?=$arPlan['NAME']?></td>
            <td>Действителен с <?=$arPlan['DATE_START']->format('d.m.Y');?></td>
        </tr>
        <tr>
            <td colspan="2">

            </td>
        </tr>
        <tr>
            <td>
                <ul>
                    <?
                    $prevLevel = 1;
                    foreach ($arResult['SECTION_SERVICES'] as $arSection) {
                        if ($arSection['DEPTH_LEVEL'] > $prevLevel) {
                            ?><ul><li class="section-service" data-section-id="<?=$arSection['ID']?>"><?=$arSection['NAME']?></li><?
                        } elseif ($arSection['DEPTH_LEVEL'] < $prevLevel) {
                            ?></ul><li class="section-service" data-section-id="<?=$arSection['ID']?>"><?=$arSection['NAME']?></li><?
                        } else {
                            ?><li class="section-service" data-section-id="<?=$arSection['ID']?>"><?=$arSection['NAME']?></li><?
                        }
                        $prevLevel = $arSection['DEPTH_LEVEL'];
                    }
                    ?>
                </ul>
            </td>
            <td id="plan-elements-<?=$arPlan['id']?>">
                <? if (isset($_REQUEST['LOAD_ELEMENTS'])) { $APPLICATION->RestartBuffer(); } ?>
                <ul>
                    <?foreach ($arResult['ELEMENT_SERVICES'] as $arElement):?>
                        <li class="element-service" data-element-id="<?=$arElement['ID']?>"><?=$arElement['NAME']?></li>
                    <?endforeach;?>
                </ul>
                <? if (isset($_REQUEST['LOAD_ELEMENTS'])) { die(); } ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <? if (isset($_REQUEST['LOAD_ITEMS']) && $arPlan['ID'] == $_REQUEST['PLAN_ID']) { $APPLICATION->RestartBuffer(); } ?>
                <table id="plan-items-<?=$arPlan['ID']?>">
                    <tr>
                        <th>ИД</th>
                        <th>Название</th>
                        <th>Ед. изм.</th>
                        <th>Количество</th>
                        <th>Цена</th>
                        <th>Сумма</th>
                    </tr>
                    <?foreach ($arPlan['ITEMS'] as $arItem):?>
                        <tr>
                            <td><?=$arItem['ID']?></td>
                            <td><?=$arResult['ELEMENTS'][$arItem['PRODUCT_ID']]['NAME']?></td>
                            <td><?=$arItem['MEASURE']?></td>
                            <td><?=$arItem['QUANTITY']?></td>
                            <td><?=$arItem['MIN_PRICE']?> / <?=$arItem['MAX_PRICE']?></td>
                            <td><?=$arItem['MIN_SUM']?> / <?=$arItem['MAX_SUM']?></td>
                        </tr>
                    <?endforeach;?>
                </table>
                <? if (isset($_REQUEST['LOAD_ITEMS']) && $arPlan['ID'] == $_REQUEST['PLAN_ID']) { die(); } ?>
            </td>
        </tr>
    </table>
<?endforeach;?>

<script>
    BX.ready(function(){
        params = <?=CUtil::PhpToJSObject(array(
            'patient' => $arResult['PATIENT'],
        ));?>;
        window.treatmentPlan = new window.treatmentPlan(params);
    });
</script>