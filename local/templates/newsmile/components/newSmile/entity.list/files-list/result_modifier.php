<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['ELEMENTS'] as &$element)
{
    if($element['FILE'] && \CFile::IsImage($element['FILE']['FILE_NAME'], $element['FILE']['CONTENT_TYPE']))
    {
        $resizedImage = \CFile::ResizeImageGet($element['FILE'], [
            'height' => 100,
            'width' => 200
        ]);

        $element['FILE']['RESIZED_SRC'] = $resizedImage['src'];
    }

    //$arResult['TYPES_FILTER'][$element['TYPE']] = $arResult['ELEMENT_FIELDS']['TYPE']['VARIANTS'][$element['TYPE']];
}

unset($element);

// группировка

$arParams['ELEMENT_GROUP_BY'] = $arParams['ELEMENT_GROUP_BY'] ?: 'DATE_CREATE';


if($arParams['ELEMENT_GROUP_BY'] == 'DATE_CREATE')
{
    foreach ($arResult['ELEMENTS'] as $element)
    {
        $dateCreate = $element['DATE_CREATE']->format('d.m.Y');
        $arResult['GROUPS'][$dateCreate][] = $element;
    }
}
else
{
    foreach ($arResult['ELEMENTS'] as $element)
    {
        foreach ($element['TEETH'] as $toothNum)
        {
            if($toothNum)
            {
                $arResult['GROUPS'][$toothNum][] = $element;
            }
        }
    }

    uksort($arResult['GROUPS'], function($toothNumA, $toothNumB) use ($arParams)
    {
        $result = 0;

        if($toothNumA > $toothNumB)
        {
            $result = 1;
        }
        elseif ($toothNumA < $toothNumB)
        {
            $result = -1;
        }

        if($arParams['ELEMENT_SORT_ORDER'] == 'desc')
        {
            $result *= -1;
        }

        return $result;
    });
}

// выбранный элемент
$firstGroupKey = array_keys($arResult['GROUPS']);
$firstGroupKey = $firstGroupKey[0];
$arResult['SELECTED_ELEMENT'] = $arResult['GROUPS'][$firstGroupKey][0];


// пагинация по группам

/*$arParams['ELEMENT_PAGE_NUM'] = $arParams['ELEMENT_PAGE_NUM'] ?: 1;

$offset = (($arParams['ELEMENT_PAGE_NUM'] - 1) * $arParams['ELEMENT_PAGE_SIZE']) + 1;

$counter = 0;

foreach ($arResult['GROUPS'] as $groupKey => $group)
{
    $counter++;
    $isLast = $counter == count($arResult['GROUPS']);

    if(($counter < $offset) || ($counter >= $offset + $arParams['ELEMENT_PAGE_SIZE']))
    {
        unset($arResult['GROUPS'][$groupKey]);

        if($isLast)
        {
            \Mmit\NewSmile\Ajax::setAreaParam('lastPageFileList', true);
        }
    }
}*/
