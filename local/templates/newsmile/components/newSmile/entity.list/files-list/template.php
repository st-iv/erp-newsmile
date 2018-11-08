<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Mmit\NewSmile;

/**
 * @var \CBitrixComponentTemplate $this
 * @var EntityListComponent $component
 */

$component = $this->getComponent();
?>

<form class="files__top-line js-files-list-filter-form" action="<?=POST_FORM_ACTION_URI?>">
    <div class="files__filter">
        <div class="filter__field">
            <label>
                Тип:
                <select class="filter__type js-files-filter-type" multiple name="TYPE">
                    <option value="ALL" selected>Все</option>

                    <?foreach ($arResult['ELEMENT_FIELDS']['TYPE']['VARIANTS'] as $typeCode => $typeTitle):?>
                        <option value="<?=$typeCode?>"><?=$typeTitle?></option>
                    <?endforeach;?>
                </select>
            </label>
        </div>
    </div>

    <div class="files__grouper">
        <label>
            Группировать по:
            <select class="filter__typ js-files-grouper">
                <option value="DATE_CREATE" selected>дате создания</option>
                <option value="TEETH">зубам</option>
            </select>
        </label>
    </div>
</form>


<div class="files__main-area">
    <div class="files__right">
        <div class="files__add-file">
            <input type="file" class="js-patient-card-add-file" data-ajax-area="patient-file-edit">
        </div>

        <div class="files__table">

            <?if($arResult['GROUPS']):?>

                <div class="table__row table__row--title">

                    <div class="table__cell table__cell--sort js-toggle-sort" data-sort-order="<?=$arParams['ELEMENT_SORT_ORDER']?>">
                        <?=$arResult['ELEMENT_FIELDS'][$arParams['ELEMENT_GROUP_BY']]['TITLE']?>
                    </div>

                    <div class="table__cell">
                        Количество файлов
                    </div>

                </div>

                <?
                $wasSelected = false;

                foreach ($arResult['GROUPS'] as $groupName => $groupItems):?>
                    <div class="table__row table__row--group">
                        <div class="table__cell">
                            <?if($arParams['ELEMENT_GROUP_BY'] == 'TEETH'):?>
                                Зуб №
                            <?endif;?>

                            <?=$groupName?>
                        </div>

                        <div class="table__cell js-files-count">
                            <?=count($groupItems)?>
                        </div>
                    </div>

                    <div class="table__row">
                        <div class="table__cell table__cell--files">

                            <?
                            foreach ($groupItems as $patientFile):?>
                                <?
                                $isSelected = !$wasSelected && ($patientFile['ID'] == $arResult['SELECTED_ELEMENT']['ID']);
                                if($isSelected)
                                {
                                    $wasSelected = true;
                                }

                                $isImage = \CFile::IsImage($patientFile['FILE']['FILE_NAME'], $patientFile['FILE']['CONTENT_TYPE']);

                                ?><a class="file js-patient-file-list-file <?=($isSelected ? 'selected' : '')?> <?= ($isImage ? '' : 'no-image') ?>"
                                     href="#"

                                   data-detail-picture="<?=($isImage ? $patientFile['FILE']['SRC'] : '')?>"
                                   data-download="<?=$patientFile['FILE']['SRC']?>"
                                   data-original-name="<?=$patientFile['FILE']['ORIGINAL_NAME']?>"
                                   data-type-code="<?=$patientFile['TYPE']?>"
                                   data-id="<?=$patientFile['ID']?>"

                                    <?foreach ($arParams['PREVIEW_FIELDS'] as $previewFieldName):?>
                                        <?
                                        $pageBlockData = [
                                            'FIELD' => $arResult['ELEMENT_FIELDS'][$previewFieldName],
                                            'VALUE' => $patientFile[$previewFieldName]
                                        ];
                                        ?>
                                        data-field-<?=str_replace('_', '-', strtolower($previewFieldName))?>="<?$component->includePageBlock('field_value', $pageBlockData);?>"
                                    <?endforeach;?>
                                >
                                    <?if($isImage):?>

                                        <img src="<?=$patientFile['FILE']['RESIZED_SRC']?>" alt="<?=$patientFile['NAME']?>">

                                    <?else:?>
                                        <div class="file__name"><?=$patientFile['NAME']?></div>
                                        <div class="file__extension"><?=strtoupper(pathinfo($patientFile['FILE']['SRC'], PATHINFO_EXTENSION))?></div>

                                    <?endif;?>
                                </a><?
                            endforeach;?>

                        </div>
                    </div>

                <?endforeach;?>

            <?endif;?>
        </div>

    </div>

    <?if($arResult['GROUPS']):?>
        <div class="files__left">
            <div class="preview">
                <?$isImage = \CFile::IsImage($arResult['SELECTED_ELEMENT']['FILE']['FILE_NAME'], $arResult['SELECTED_ELEMENT']['FILE']['CONTENT_TYPE']);?>

                <img class="js-files-preview" src="<?=$arResult['SELECTED_ELEMENT']['FILE']['SRC']?>" alt="<?=$arResult['SELECTED_ELEMENT']['NAME']?>" <?=($isImage ? '' : 'style="display:none;"')?>>


                <div class="file-info">
                    <?$exceptFields = ['ID', 'FILE', 'NAME_BY_TEMPLATE']?>

                    <?foreach ($arResult['SELECTED_ELEMENT'] as $fieldCode => $fieldValue):?>
                        <?
                        $field = $arResult['ELEMENT_FIELDS'][$fieldCode];
                        $pageBlockData = [
                            'FIELD' => $field,
                            'VALUE' => $fieldValue
                        ];
                        ?>

                        <?if(!in_array($fieldCode, $arParams['PREVIEW_FIELDS'])) continue;?>

                        <div class="file-info__field" data-field-code="<?=$fieldCode?>">
                            <span class="field-title"><?=$field['TITLE']?></span>:
                            <span class="field-value"><?$component->includePageBlock('field_value', $pageBlockData);?></span>
                        </div>

                    <?endforeach;?>
                </div>
            </div>
        </div>
    <?endif;?>
</div>


