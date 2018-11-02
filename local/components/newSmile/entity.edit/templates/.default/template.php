<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?

/**
 * @var $component EntityEditComponent
 */
$component = $this->getComponent();
?>
<form action="<?=POST_FORM_ACTION_URI?>" class="entity-edit-form" method="post">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="ajax" value="Y">
    <input type="hidden" name="action" value="<?=$arResult['ACTION']?>">

    <?foreach($arResult['FIELDS'] as $field):?>

        <?$component->includePageBlock('field_row', $field);?>

    <?endforeach;?>

    <div class="edit-form__row">
        <button type="submit">
            <?if($arResult['ACTION'] == 'add'):?>
                Добавить
            <?else:?>
                Сохранить
            <?endif;?>
        </button>
    </div>
</form>

