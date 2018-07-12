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

<?foreach ($arResult['TREATMENT_PLAN'] as $arPlan):?>
    <table border="1" cellspacing="0" cellpadding="2" class="treatment-plan" data-plan-id="<?=$arPlan['ID']?>">
        <tr>
            <td><?=$arPlan['NAME']?></td>
            <td>Действителен с <?=$arPlan['DATE_START']->format('d.m.Y');?></td>
        </tr>
        <tr>
            <td colspan="2">
                <table class="claw-list">
                    <tr>
                        <td>В</td>
                        <td data-measure-id="в.ч.">в.ч.</td>
                        <td data-measure-id="18">18</td>
                        <td data-measure-id="17">17</td>
                        <td data-measure-id="16">16</td>
                        <td data-measure-id="15">15</td>
                        <td data-measure-id="14">14</td>
                        <td data-measure-id="13">13</td>
                        <td data-measure-id="12">12</td>
                        <td data-measure-id="11">11</td>
                        <td data-measure-id="21">21</td>
                        <td data-measure-id="22">22</td>
                        <td data-measure-id="23">23</td>
                        <td data-measure-id="24">24</td>
                        <td data-measure-id="25">25</td>
                        <td data-measure-id="26">26</td>
                        <td data-measure-id="27">27</td>
                        <td data-measure-id="28">28</td>
                        <td rowspan="2">Очистить</td>
                        <td>З</td>
                    </tr>
                    <tr>
                        <td>Д</td>
                        <td data-measure-id="н.ч.">н.ч.</td>
                        <td data-measure-id="48">48</td>
                        <td data-measure-id="47">47</td>
                        <td data-measure-id="46">46</td>
                        <td data-measure-id="45">45</td>
                        <td data-measure-id="44">44</td>
                        <td data-measure-id="43">43</td>
                        <td data-measure-id="42">42</td>
                        <td data-measure-id="41">41</td>
                        <td data-measure-id="31">31</td>
                        <td data-measure-id="32">32</td>
                        <td data-measure-id="33">33</td>
                        <td data-measure-id="34">34</td>
                        <td data-measure-id="35">35</td>
                        <td data-measure-id="36">36</td>
                        <td data-measure-id="37">37</td>
                        <td data-measure-id="38">38</td>
                        <td>К</td>
                    </tr>
                </table>
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
                <? if (isset($_REQUEST['LOAD_ITEMS'])) { $APPLICATION->RestartBuffer(); } ?>
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
                <? if (isset($_REQUEST['LOAD_ITEMS'])) { die(); } ?>
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