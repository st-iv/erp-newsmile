<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<div class="card__main-content">
    <div class="card__header">
        <div class="card__tabs-1lvl">
            <div class="card__tab-1lvl" data-target="main-info">Основная информация</div>
            <div class="card__tab-1lvl" data-target="plans">Планы лечения</div>
            <div class="card__tab-1lvl" data-target="course">Курс лечения</div>
        </div>
    </div>


    <div class="card__workarea">
        <div class="card__tab-content card__tab-content--active" data-tab-code="main-info">
            <?
            $APPLICATION->IncludeComponent(
                "newSmile:entity.edit",
                "",
                Array(
                    'DATA_MANAGER_CLASS' => $arParams['DATA_MANAGER_CLASS_ELEMENT'],
                    'EDITABLE_FIELDS' => [],
                    'SELECT_FIELDS' => $arParams['ELEMENT_VIEW_FIELDS'],
                    'ENTITY_ID' => $arResult['VARIABLES']['ELEMENT_ID'],
                    'ADD_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['add_element'],
                    'REVERSE_REFERENCES' => $arParams['REVERSE_REFERENCES']
                ),
                $component
            );
            ?>
        </div>

        <div class="card__tab-content" data-tab-code="plans">
            планы лечения!
        </div>

        <div class="card__tab-content" data-tab-code="course">
            курсы лечения!
        </div>
    </div>
</div>

