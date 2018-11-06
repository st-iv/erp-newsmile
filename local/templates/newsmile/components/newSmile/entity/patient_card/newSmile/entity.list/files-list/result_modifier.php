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

    if(!$arResult['SELECTED_ELEMENT'])
    {
        $arResult['SELECTED_ELEMENT'] = $element;
    }

    $arResult['TYPES_FILTER'][$element['TYPE']] = $arResult['ELEMENT_FIELDS']['TYPE']['VARIANTS'][$element['TYPE']];
}

unset($element);

foreach ($arResult['ELEMENTS'] as $element)
{
    $dateCreate = $element['DATE_CREATE']->format('d.m.Y');
    $arResult['GROUPS'][$dateCreate][] = $element;
}

