<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<?
/**
 * @var string $pageBlockName
 * @var array $pageBlockData
 * @var array $arParams
 * @var \CBitrixComponent $component
 */

if($pageBlockData['EDITABLE'] || ($pageBlockData['TYPE'] == 'hidden')):
    switch($pageBlockData['TYPE']):
        case 'enum':
        case 'reference':?>
            <select name="<?=$pageBlockData['INPUT_NAME']?>" <?=($pageBlockData['DISABLED'] ? 'disabled' : '')?>>

                <?if(!$pageBlockData['REQUIRED']):?>
                    <option value="0">нет</option>
                <?endif;?>

                <?foreach ($pageBlockData['VARIANTS'] as $itemValue => $item):?>
                    <option value="<?=$itemValue?>" <?=($item['SELECTED'] ? 'selected' : '')?>>
                        <?=$item['VARIANT_TITLE'] ?: $item['NAME']?>
                    </option>
                <?endforeach;?>
            </select>
            <?break;?>

        <?case 'boolean':?>
            <input type="checkbox" name="<?=$pageBlockData['INPUT_NAME']?>"
                   value="<?=$pageBlockData['TRUE_VALUE']?>" <?=($pageBlockData['CHECKED'] ? 'checked' : '')?> <?=($pageBlockData['DISABLED'] ? 'disabled' : '')?>>
            <?break;?>

        <?case 'datetime':?>
            <?
            /**
             * @var \Bitrix\Main\Type\DateTime $value
             */
            $value = $pageBlockData['VALUE'] ? $pageBlockData['VALUE']->format('Y-m-d\TH:i:s') : '';
            ?>
            <input type="datetime-local" name="<?=$pageBlockData['INPUT_NAME']?>" value="<?=$value?>" <?=($pageBlockData['DISABLED'] ? 'disabled' : '')?>>
            <?break;?>



        <?case 'date':?>
            <?
            /**
             * @var \Bitrix\Main\Type\Date $value
             */
            $value = $pageBlockData['VALUE'];
            ?>
            <input type="date" name="<?=$pageBlockData['INPUT_NAME']?>" value="<?=$value->format('Y-m-d')?>" <?=($pageBlockData['DISABLED'] ? 'disabled' : '')?>>
            <?break;?>

        <?case 'hidden':?>
            <input type="hidden" name="<?=$pageBlockData['INPUT_NAME']?>" value="<?=$pageBlockData['VALUE']?>" <?=($pageBlockData['DISABLED'] ? 'disabled' : '')?>>
            <?break;?>

        <?default:?>
            <input type="text" name="<?=$pageBlockData['INPUT_NAME']?>" value="<?=$pageBlockData['VALUE']?>" <?=($pageBlockData['REQUIRED'] ? 'required' : '') ?> <?=($pageBlockData['DISABLED'] ? 'disabled' : '')?>>

    <?endswitch;?>

<?else:?>
    <?
    switch($pageBlockData['TYPE'])
    {
        case 'hidden':
            break;

        case 'datetime':
            /**
             * @var \Bitrix\Main\Type\DateTime $value
             */
            echo $pageBlockData['VALUE'] ? $pageBlockData['VALUE']->format('Y-m-d\TH:i:s') : '';
            break;

        case 'date':
            echo $pageBlockData['VALUE'] ? $pageBlockData['VALUE']->format('Y-m-d') : '';
            break;

        default:
            echo $pageBlockData['VALUE'];
    }
    ?>
<?endif;?>
