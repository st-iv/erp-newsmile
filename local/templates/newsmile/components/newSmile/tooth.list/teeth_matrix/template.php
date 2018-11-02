<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
/**
 * @var $this \CBitrixComponentTemplate
 */

$uniqueId = 'teeth-matrix-' . \Mmit\NewSmile\Helpers::uniqid();
?>
<div class="teeth-matrix" id="<?=$uniqueId?>">
    <div class="teeth-matrix__control-panel">
        <select class="teeth__group js-teeth-matrix-select-group">
            <?foreach ($arResult['TEETH'] as $teethGroupCode => $teethGroupInfo):?>
                <option value="<?=$teethGroupCode?>" <?=($teethGroupInfo['SELECTED'] ? 'selected' : '')?>>
                    <?=($teethGroupCode == 'ADULT' ? 'Взрослый' : 'Ребенок')?>
                </option>
            <?endforeach;?>
        </select>
    </div>

    <div class="teeth-matrix__body">

        <?foreach ($arResult['TEETH'] as $teethGroupCode => $teethGroupInfo):?>

            <div class="teeth-matrix__jaws teeth-matrix__jaws--<?=strtolower($teethGroupCode)?> <?=$teethGroupInfo['SELECTED'] ? 'selected' : ''?>"
                 data-group-code="<?=$teethGroupCode?>">

                <?foreach ($teethGroupInfo['JAWS'] as $jawCode => $teeth):?>

                    <div class="teeth-matrix__jaw teeth-matrix__jaw--<?=strtolower($jawCode)?>" data-jaw-code="<?=$jawCode?>">

                        <?foreach ($teeth as $toothNumber => $toothInfo):?>

                            <div class="jaw__tooth <?= ($toothInfo['SELECTED'] ? 'selected' : '') ?>" data-number="<?=$toothNumber?>">
                                <?=$toothNumber?>
                            </div>

                        <?endforeach;?>

                    </div>

                <?endforeach;?>

            </div>

        <?endforeach;?>

        <div class="teeth-matrix__select-jaws">
            <button type="button" class="select-jaw js-select-jaw-button" data-target="TOP">Верхняя челюсть</button>
            <button type="button" class="select-jaw js-select-jaw-button" data-target="BOTTOM">Нижняя челюсть</button>
        </div>

    </div>

    <input type="hidden" name="<?=$arParams['FIELD_NAME']?>" value="" class="js-teeth-matrix-input">
</div>

<?
$jsObjectName = $arParams['JS_OBJECT_NAME'] ?: 'teethMatrix';
?>

<script>
    var <?=$jsObjectName?> = new TeethMatrix('#<?=$uniqueId?>');
</script>