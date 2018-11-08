<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?

use Mmit\NewSmile\Ajax;

/**
 * @var $component EntityEditComponent
 */
$component = $this->getComponent();
?>

<div class="patient-file-edit">
    <div class="form__header">
        <div class="form__header-top">
            <div class="form__title">
                <?if($arResult['ACTION'] == 'add'):?>
                    Добавление файла
                <?else:?>
                    Изменение свойств файла
                <?endif;?>
            </div>

            <div class="form__buttons">
                <button class="js-patient-file-save-button">
                    Сохранить
                </button>
            </div>
        </div>

        <div class="form__header-bottom">
            Пациент <span class="patient-name">Иванов Иван Иванович</span><span class="patient-age">50 лет, 1978 г.р.</span>
        </div>
    </div>

    <div class="form__workarea">

        <form action="<?=POST_FORM_ACTION_URI?>" class="entity-edit-form" method="post">

            <div class="main-info">
                <?
                $isImage = \CFile::IsImage($arResult['FIELDS']['FILE']['VALUE']['FILE_NAME']);
                ?>

                <div class="main-info__preview-container">
                    <img src="<?= ($isImage ? $arResult['FIELDS']['FILE']['VALUE']['SRC'] : '') ?>" class="main-info__file-preview js-patient-file-edit-preview">
                    <div class="main-info__file-no-preview js-patient-file-edit-no-preview" <?=($isImage ? 'style="display: none;"' : '')?>>
                        Файл не является изображением, просмотр недоступен
                    </div>
                </div>


                <div class="main-info__right-side">
                    <div class="main-info__operations">

                    </div>

                    <div class="main-info__props">

                        <?=bitrix_sessid_post()?>
                        <input type="hidden" name="ajax" value="Y">
                        <input type="hidden" name="action" value="<?=$arResult['ACTION']?>">

                        <?foreach($arResult['FIELDS'] as $field):?>
                            <?
                            if(($field['NAME'] !== 'TEETH') && ($field['NAME'] !== 'FILE'))
                            {
                                $component->includePageBlock('field_row', $field);
                            }
                            ?>
                        <?endforeach;?>
                    </div>
                </div>
            </div>

            <?if($arResult['FIELDS']['TEETH'] && $arResult['FIELDS']['TEETH']['SERIALIZED']):?>
                <div class="form__teeth">
                    <?
                    $APPLICATION->IncludeComponent(
                        "newSmile:tooth.list",
                        'teeth_matrix',
                        Array(
                            'SELECTED' => $arResult['FIELDS']['TEETH']['VALUE'],
                            'FIELD_NAME' => 'TEETH'
                        ),
                        $component
                    );
                    ?>
                </div>
            <?endif;?>

            <input type="hidden" value="<?=$arResult['FIELDS']['FILE']['VALUE']['ID']?>" name="FILE" class="js-file-input-field">
            <input type="hidden" value="<?=$arParams['ENTITY_ID']?>" name="FILE_ID">
        </form>
    </div>
</div>




