<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?

/**
 * @var $component EntityEditComponent
 */
$component = $this->getComponent();
?>
<form action="<?=POST_FORM_ACTION_URI?>" class="entity-edit-form js-ajax-load"
      method="post" id="patient-card-fields">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="ajax" value="Y">
    <input type="hidden" name="action" value="<?=$arResult['ACTION']?>">

    <div class="edit-form__groups">
        <?foreach($arResult['GROUPS'] as $group):?>

            <div class="edit-form__group">
                <div class="group__title"><?=$group['TITLE']?></div>
                <div class="group__content">

                    <?foreach ($group['FIELDS'] as $field):?>
                        <?$component->includePageBlock('field_row', $field);?>
                    <?endforeach;?>

                </div>
            </div>

        <?endforeach;?>
    </div>

    <?if($arParams['EDITABLE_FIELDS']):?>

        <div class="edit-form__row">
            <button type="submit">
                <?if($arResult['ACTION'] == 'add'):?>
                    Добавить
                <?else:?>
                    Сохранить
                <?endif;?>
            </button>
        </div>

    <?endif;?>
</form>

