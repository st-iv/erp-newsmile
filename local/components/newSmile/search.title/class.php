<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Mmit\NewSmile;
use Bitrix\Main\Loader;

class NewSmileSearchTitleComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        if(!$arParams['USE_LANGUAGE_GUESS'])
        {
            $arParams['USE_LANGUAGE_GUESS'] = 'Y';
        }

        $arParams['MIN_QUERY_LENGTH'] = (int)$arParams['MIN_QUERY_LENGTH'];

        if($arParams['MIN_QUERY_LENGTH'] <= 0)
        {
            $arParams['MIN_QUERY_LENGTH'] = 3;
        }

        $arParams['TOP_COUNT'] = (int)$arParams['TOP_COUNT'];

        if($arParams['TOP_COUNT'] <= 0)
        {
            $arParams['TOP_COUNT'] = 5;
        }

        return $arParams;
    }

    protected function processRequest(\Bitrix\Main\HttpRequest $request)
    {
        $query = ltrim($request['q']);

        if(empty($query) || !Loader::includeModule('search')) return;

        CUtil::decodeURIComponent($query);

        $query = urldecode($query);

        $this->arResult['ALT_QUERY'] = '';
        if($this->arParams['USE_LANGUAGE_GUESS'] !== 'N')
        {
            $arLang = CSearchLanguage::GuessLanguage($query);
            if(is_array($arLang) && $arLang["from"] != $arLang["to"])
            {
                $this->arResult['ALT_QUERY'] = CSearchLanguage::ConvertKeyboardLayout($query, $arLang["from"], $arLang["to"]);
            }
        }

        $this->arResult['QUERY'] = $query;
        $this->arResult['PHRASE'] = stemming_split($query, LANGUAGE_ID);

        $query = preg_replace(
            '/(\S+)/',
            '"\\1"',
            ($this->arResult['ALT_QUERY']  ? $this->arResult['ALT_QUERY'] : $query)
        );


        $this->arResult['CATEGORIES'] = array();

        foreach ($this->arParams['CATEGORIES'] as $categoryCode => $category)
        {
            $obTitle = new CSearchTitle();
            $obTitle->setMinWordLength($this->arParams['MIN_QUERY_LENGTH']);

            $isSuccess = $obTitle->Search(
                $query,
                $this->arParams['TOP_COUNT'],
                array(
                    'MODULE' => $category['MODULE'],
                    'PARAM1' => $categoryCode
                )
            );

            if($isSuccess && $obTitle->SelectedRowsCount())
            {
                $this->arResult['CATEGORIES'][$categoryCode]['TITLE'] = $category['TITLE'];
                $this->writeSearchResults($obTitle, $categoryCode, $category);
            }
        }
    }

    protected function writeSearchResults(CSearchTitle $searchTitle, $categoryCode, array $categoryParams = array())
    {
        $category =& $this->arResult['CATEGORIES'][$categoryCode];
        $queryElementsIds = array();

        while($searchResult = $searchTitle->Fetch())
        {
            if(preg_match('/^' . $categoryCode . '_([a-z_]+)_' . $searchResult['PARAM2'] .'$/', $searchResult['ITEM_ID'], $matches))
            {
                $subCategoryName = strtoupper($matches[1]);
            }
            else
            {
                $subCategoryName = 'MAIN';
            }
            $category['SUBCATEGORIES'][$subCategoryName][$searchResult['PARAM2']] = array(
                'ID' => $searchResult['PARAM2'],
                'SEARCH_ENTRY' => $searchResult['NAME']
            );

            $queryElementsIds[] = $searchResult['PARAM2'];
        }


        // добираем поля элемента, указанные в параметре категории FIELDS
        if($categoryParams['FIELDS'] && $categoryParams['ENTITY'])
        {
            $dataManager = $categoryParams['ENTITY'] . 'Table';

            if(!NewSmile\Orm\Helper::isDataManagerClass($dataManager)) return;

            $select = $categoryParams['FIELDS'];
            $select[] = 'ID';

            /**
             * @var \Bitrix\Main\Entity\DataManager $dataManager
             */
            $dbElements = $dataManager::getList(array(
                'select' => $select,
                'filter' => $queryElementsIds
            ));

            while($element = $dbElements->fetch())
            {
                foreach ($category['SUBCATEGORIES'] as &$items)
                {
                    if($items[$element['ID']])
                    {
                        $items[$element['ID']] = array_merge($items[$element['ID']], $element);
                    }
                }

                unset($items);
            }
        }

        unset($category);
    }

    public function executeComponent()
    {
        try
        {
            Loader::includeModule('mmit.newsmile');

            if(NewSmile\Ajax::isAjaxQuery())
            {
                $this->processRequest($this->request);
            }

            $this->includeComponentTemplate();
        }
        catch (Exception $e)
        {
            ShowError($e->getMessage());
        }
    }
}