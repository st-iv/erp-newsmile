<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
use Mmit\NewSmile;

$patient = \Mmit\NewSmile\PatientCardTable::getByPrimary($arResult['VARIABLES']['ELEMENT_ID'], [
    'select' => ['NUMBER', 'PERSONAL_BIRTHDAY', 'LAST_NAME', 'NAME', 'SECOND_NAME']
])->fetch();

$patientEditUrl = $arResult['FOLDER'] . \CComponentEngine::makePathFromTemplate(
    $arResult['URL_TEMPLATES']['edit_element'],
    ['ELEMENT_ID' => $arResult['VARIABLES']['ELEMENT_ID']]
);

?>
<div class="card__main-content">
    <div class="card__header">

        <div class="card__header-top">
            <div class="card__header-info">
                <div class="header__card-number">
                    Карточка пациента - <?=$patient['NUMBER']?>
                </div>
                <div class="header__main-info">
                    <div class="header__fio">
                        <?=NewSmile\Helpers::getFio($patient)?>
                    </div>
                    <div class="header__age">
                        <?=NewSmile\Date\Helper::getAge($patient['PERSONAL_BIRTHDAY']) . $patient['PERSONAL_BIRTHDAY']->format(', Y г.р.')?>
                    </div>
                </div>
            </div>

            <div class="card__header-buttons">
                <button class="js-toggle-edit-mode" data-alt-title="Просмотр" data-mode="edit">
                    Редактировать
                </button>
            </div>
        </div>


        <div class="card__tabs-1lvl">
            <div class="card__tab-1lvl active" data-target="main-info">Главная</div>
            <div class="card__tab-1lvl" data-target="plans">Планы лечения</div>
            <div class="card__tab-1lvl" data-target="course">Курс лечения</div>
            <div class="card__tab-1lvl" data-target="files">Файлы</div>
        </div>
    </div>


    <div class="card__workarea">
        <div class="card__tab-content active card__main-info" data-tab-code="main-info"
             data-edit-url="<?=$patientEditUrl?>" data-view-url="<?=$APPLICATION->GetCurPage()?>">

            <?
            NewSmile\Ajax::start('patient-main-info');

            $APPLICATION->IncludeComponent(
                "newSmile:entity.edit",
                "patient_main_info",
                Array(
                    'DATA_MANAGER_CLASS' => $arParams['DATA_MANAGER_CLASS_ELEMENT'],
                    'EDITABLE_FIELDS' => [],
                    'SELECT_FIELDS' => $arParams['ELEMENT_VIEW_FIELDS'],
                    'ENTITY_ID' => $arResult['VARIABLES']['ELEMENT_ID'],
                    'ADD_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['add_element'],
                    'REVERSE_REFERENCES' => $arParams['REVERSE_REFERENCES'],
                    'PARENT_TEMPLATE' => '.default',
                    'GROUPS' => $arParams['ELEMENT_FIELD_GROUPS']
                ),
                $component
            );

            NewSmile\Ajax::finish();
            ?>
        </div>

        <div class="card__tab-content" data-tab-code="plans">
            планы лечения!
        </div>

        <div class="card__tab-content" data-tab-code="course">
            курсы лечения!
        </div>

        <div class="card__tab-content card__files" data-tab-code="files">

            <?
            \Bitrix\Main\Diag\Debug::writeToFile('$_REQUEST');
            \Bitrix\Main\Diag\Debug::writeToFile($_REQUEST['TYPE']);
            ?>

            <?NewSmile\Ajax::start('patient-files');?>

            <?
            $filter = [
                'PATIENT_ID' => $arResult['VARIABLES']['ELEMENT_ID']
            ];

            if($_REQUEST['TYPE'])
            {
                $filter['TYPE'] = explode(',', $_REQUEST['TYPE']);
            }


            $APPLICATION->IncludeComponent(
                "newSmile:entity.list",
                "files-list",
                Array(
                    'DATA_MANAGER_CLASS_ELEMENT' => '\\Mmit\\NewSmile\\FileTable',
                    'ELEMENT_QUERY_PARAMS' => [
                        'select' => ['NAME', 'TYPE', 'DATE_CREATE', 'TEETH', 'FILE'],
                        'order' => [
                            'DATE_CREATE' => 'desc'
                        ],
                        'filter' => $filter
                    ],
                    'PREVIEW_FIELDS' => ['NAME', 'TYPE', 'DATE_CREATE', 'TEETH']
                ),
                $component
            );?>

            <?NewSmile\Ajax::finish();?>

        </div>
    </div>
</div>


