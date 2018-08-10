<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); ?>
<?$APPLICATION->IncludeComponent(
    "bitrix:search.title",
    ".default",
    array(
        "CATEGORY_0" => array(
            0 => "no",
        ),
        "CATEGORY_0_TITLE" => "",
        "CHECK_DATES" => "N",
        "CONTAINER_ID" => "title-search",
        "INPUT_ID" => "title-search-input",
        "NUM_CATEGORIES" => "1",
        "ORDER" => "date",
        "PAGE" => "#SITE_DIR#search/index.php",
        "SHOW_INPUT" => "Y",
        "SHOW_OTHERS" => "N",
        "TOP_COUNT" => "5",
        "USE_LANGUAGE_GUESS" => "Y",
        "COMPONENT_TEMPLATE" => ".default"
    ),
    false
);?>
<?$APPLICATION->IncludeComponent(
    "newSmile:patient.card.list",
    "",
    Array(
    )
);?>