<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<?
/**
 * @var string $pageBlockName
 * @var array $data
 * @var array $arParams
 * @var \CBitrixComponent $component
 */

if($data['EDITABLE'] || ($data['TYPE'] == 'hidden')):
    switch($data['TYPE']):
        case 'enum':
        case 'reference':?>
            <select name="<?=$data['INPUT_NAME']?>" <?=($data['DISABLED'] ? 'disabled' : '')?>>

                <?if(!$data['REQUIRED']):?>
                    <option value="0">нет</option>
                <?endif;?>

                <?foreach ($data['VARIANTS'] as $itemValue => $item):?>
                    <option value="<?=$itemValue?>" <?=($item['SELECTED'] ? 'selected' : '')?>>
                        <?=$item['VARIANT_TITLE'] ?: $item['NAME']?>
                    </option>
                <?endforeach;?>
            </select>
            <?break;?>

        <?case 'boolean':?>
            <input type="checkbox" name="<?=$data['INPUT_NAME']?>"
                   value="<?=$data['TRUE_VALUE']?>" <?=($data['CHECKED'] ? 'checked' : '')?> <?=($data['DISABLED'] ? 'disabled' : '')?>>
            <?break;?>

        <?case 'datetime':?>
            <?
            /**
             * @var \Bitrix\Main\Type\DateTime $value
             */
            $value = $data['VALUE'] ? $data['VALUE']->format('Y-m-d\TH:i:s') : '';
            ?>
            <input type="datetime-local" name="<?=$data['INPUT_NAME']?>" value="<?=$value?>" <?=($data['DISABLED'] ? 'disabled' : '')?>>
            <?break;?>



        <?case 'date':?>
            <?
            /**
             * @var \Bitrix\Main\Type\Date $value
             */
            $value = $data['VALUE'];
            ?>
            <input type="date" name="<?=$data['INPUT_NAME']?>" value="<?=$value->format('Y-m-d')?>" <?=($data['DISABLED'] ? 'disabled' : '')?>>
            <?break;?>

        <?case 'hidden':?>
            <input type="hidden" name="<?=$data['INPUT_NAME']?>" value="<?=$data['VALUE']?>" <?=($data['DISABLED'] ? 'disabled' : '')?>>
            <?break;?>

        <?default:?>
            <input type="text" name="<?=$data['INPUT_NAME']?>" value="<?=$data['VALUE']?>" <?=($data['REQUIRED'] ? 'required' : '') ?> <?=($data['DISABLED'] ? 'disabled' : '')?>>

    <?endswitch;?>

<?else:?>
    <?
    switch($data['TYPE'])
    {
        case 'hidden':
            break;

        case 'datetime':
            /**
             * @var \Bitrix\Main\Type\DateTime $value
             */
            echo $data['VALUE'] ? $data['VALUE']->format('Y-m-d\TH:i:s') : '';
            break;

        case 'date':
            echo $data['VALUE'] ? $data['VALUE']->format('Y-m-d') : '';
            break;

        default:
            echo $data['VALUE'];
    }
    ?>
<?endif;?>
