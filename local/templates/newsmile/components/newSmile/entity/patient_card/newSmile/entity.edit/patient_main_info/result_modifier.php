<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['FIELDS'] as &$field)
{
    if($field['TYPE'] == 'reversereference')
    {
        $defaultItem = array();

        foreach ($field['FIELDS'] as $subField)
        {
            $defaultItem[$subField['NAME']] = $subField['DEFAULT'];
        }

        $field['ITEMS'][] = $defaultItem;
    }
}

unset($field);

// распределение полей по группам
/*foreach ($arParams['GROUPS'] as $groupTitle => $groupFieldsNames)
{
    $groupFields = [];
    pr($groupTitle);

    foreach ($groupFieldsNames as $fieldName)
    {
        if(isset($arResult['FIELDS'][$fieldName]))
        {
            $groupFields[$fieldName] = $arResult['FIELDS'][$fieldName];
        }
    }

    if($groupFields)
    {
        $arResult['GROUPS'][] = [
            'TITLE' => $groupTitle,
            'FIELDS' => $groupFields
        ];
    }
}*/

foreach ($arParams['GROUPS'] as $group)
{
    $groupFieldsNames = array_flip($group['FIELDS']);
    $groupFields = array_filter($arResult['FIELDS'], function($fieldName) use ($groupFieldsNames)
    {
        return isset($groupFieldsNames[$fieldName]);
    }, ARRAY_FILTER_USE_KEY);

    if($groupFields)
    {
        $arResult['GROUPS'][] = [
            'TITLE' => $group['TITLE'],
            'FIELDS' => $groupFields
        ];
    }
}


$arResult['FIO'] = \Mmit\NewSmile\Helpers::getFio([
    'NAME' => $arResult['FIELDS']['NAME']['VALUE'],
    'LAST_NAME' => $arResult['FIELDS']['LAST_NAME']['VALUE'],
    'SECOND_NAME' => $arResult['FIELDS']['SECOND_NAME']['VALUE'],
]);

$arResult['AGE'] = \Mmit\NewSmile\Date\Helper::getAge($arResult['FIELDS']['PERSONAL_BIRTHDAY']['VALUE']);

