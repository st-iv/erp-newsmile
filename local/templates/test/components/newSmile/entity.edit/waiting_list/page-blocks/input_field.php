<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<?
/**
 * @var string $pageBlockCode
 * @var array $data
 * @var \EntityEditComponent $component
 */
if($data['NAME'] == 'DATE'):
?>
    <input type="hidden" name="DATE[]">
    <table class="calendar" border=1 cellspacing=0 cellpadding=2>
        <tr><td>Пн</td><td>Вт</td><td>Ср</td><td>Чт</td><td>Пт</td><td>Сб</td><td>Вс</td><tr>
        <?foreach ($data['CALENDAR'] as $arWeek):?>
            <tr class="items-calendar">
                <?foreach ($arWeek as $arDay):?>
                    <td data-date="<?=$arDay;?>">
                        <a>
                            <strong><?=date('d', strtotime($arDay))?></strong>
                            <br><?=date('M', strtotime($arDay))?>
                        </a>
                    </td>
                <?endforeach;?>
            </tr>
        <?endforeach;?>
    </table>
<?
else:
    $component->includeOriginalPageBlock($pageBlockCode, $data);
endif;
?>
