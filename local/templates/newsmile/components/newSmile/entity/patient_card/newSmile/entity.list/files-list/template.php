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
                    <option value="" selected>Все</option>

                    <?foreach ($arResult['TYPES_FILTER'] as $typeCode => $typeTitle):?>
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

            <div class="table__row table__row--title">
                <div class="table__col-group">
                    <div class="table__cell">
                        <?=$arResult['ELEMENT_FIELDS']['DATE_CREATE']['TITLE']?>
                    </div>

                    <div class="table__cell">
                        Количество файлов
                    </div>
                </div>
            </div>

            <?foreach ($arResult['GROUPS'] as $groupName => $groupItems):?>
                <div class="table__row table__row--group">
                    <div class="table__col-group">
                        <div class="table__cell">
                            <?=$groupName?>
                        </div>

                        <div class="table__cell js-files-count">
                            <?=count($groupItems)?>
                        </div>
                    </div>
                </div>

                <div class="table__row">
                    <div class="table__cell table__cell--files">

                        <?foreach ($groupItems as $patientFile):?>
                            <?
                            $isSelected = ($patientFile['ID'] == $arResult['SELECTED_ELEMENT']['ID']);
                            $isImage = \CFile::IsImage($patientFile['FILE']['FILE_NAME'], $patientFile['FILE']['CONTENT_TYPE']);
                            ?>

                            <a class="file <?=($isSelected ? 'selected' : '')?>" href="#"

                               data-detail-picture="<?=($isImage ? $patientFile['FILE']['SRC'] : '')?>"
                               data-type-code="<?=$patientFile['TYPE']?>"

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
                                <?if(\CFile::IsImage($patientFile['FILE']['FILE_NAME'], $patientFile['FILE']['CONTENT_TYPE'])):?>

                                    <img src="<?=$patientFile['FILE']['RESIZED_SRC']?>" alt="<?=$patientFile['NAME']?>">

                                <?endif;?>
                            </a>
                        <?endforeach;?>

                    </div>
                </div>

            <?endforeach;?>
        </div>

    </div>

    <div class="files__left">
        <div class="preview">
            <?$isImage = \CFile::IsImage($arResult['SELECTED_ELEMENT']['FILE']['FILE_NAME'], $arResult['SELECTED_ELEMENT']['FILE']['CONTENT_TYPE']);?>

            <img class="js-files-preview" src="<?=$arResult['SELECTED_ELEMENT']['FILE']['SRC']?>" alt="<?=$arResult['SELECTED_ELEMENT']['NAME']?>" <?=($isImage ? '' : 'style="display:none;"')?>>

            <div class="no-preview" <?=($isImage ? 'style="display:none;"' : '')?>>
                Файл не является изображением, предпросмотр невозможен
            </div>

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
</div>

