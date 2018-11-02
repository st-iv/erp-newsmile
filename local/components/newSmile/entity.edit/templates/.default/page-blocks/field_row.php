<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>


<?
/**
 * @var string $pageBlockName
 * @var array $pageBlockData
 */

$field = $pageBlockData;
?>

<?if($field['TYPE'] == 'hidden'):?>
    <?$component->includePageBlock('field_input', $field);?>
    <?return;?>
<?endif;?>

<div class="edit-form__row">

    <?if($field['TYPE'] != 'reversereference'):?>

        <div class="field__title">
            <?=$field['TITLE']?>:
        </div>

        <div class="field__input">
            <?$component->includePageBlock('field_input', $field);?>
        </div>

    <?else:?>
        <div class="edit-form__row edit-form__row--table">

            <b><?=$field['TITLE']?></b>

            <table class="reverse-reference">
                <tr>
                    <?foreach ($field['FIELDS'] as $subField):?>
                        <?if($subField['TYPE'] == 'hidden') continue;?>
                        <th><?=$subField['TITLE']?></th>
                    <?endforeach;?>
                </tr>

                <?foreach ($field['ITEMS'] as $item):?>
                    <?$isTemplate = empty($item['ID']);?>
                    <tr class="<?=($isTemplate ? 'template-field js-template-field' : '')?>">
                        <?foreach ($item as $fieldName => $fieldValue):?>
                            <?
                            $outputField = $field['FIELDS'][$fieldName];
                            $outputField['VALUE'] = $fieldValue;

                            $outputField['DISABLED'] = $isTemplate;


                            if($outputField['TYPE'] == 'hidden'):?>
                                <?$component->includePageBlock('field_input', $outputField);?>
                            <?else:?>
                                <?
                                if($outputField['TYPE'] == 'reference')
                                {
                                    $outputField['VARIANTS'][$fieldValue]['SELECTED'] = true;
                                }
                                ?>
                                <td>
                                    <?$component->includePageBlock('field_input', $outputField);?>
                                </td>
                            <?endif;?>
                        <?endforeach;?>
                    </tr>
                <?endforeach;?>

            </table>

        </div>
    <?endif;?>
</div>
