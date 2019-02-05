<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
use Mmit\NewSmile\Application;

\Bitrix\Main\Loader::includeModule('mmit.newsmile');

$isNewTemplateVersion = ($APPLICATION->GetCurPage() == '/');
?>
<?$asset = \Bitrix\Main\Page\Asset::getInstance();?>
<!DOCTYPE html>
<html lang="<?=LANGUAGE_ID?>">
<head>
    <title><?$APPLICATION->ShowTitle()?></title>
    <?$APPLICATION->ShowHead()?>

    <?
    if(!$isNewTemplateVersion)
    {
        $asset->addCss(SITE_TEMPLATE_PATH . '/css/bootstrap.min.css');
        $asset->addCss('https://fonts.googleapis.com/css?family=Rubik:300,400,500 subset=cyrillic');
        $asset->addCss(SITE_TEMPLATE_PATH . '/css/jquery.mCustomScrollbar.css');
        $asset->addCss(SITE_TEMPLATE_PATH . '/css/jquery-ui.min.css');
        $asset->addCss(SITE_TEMPLATE_PATH . '/css/jquery.contextMenu.min.css');
        $asset->addCss(SITE_TEMPLATE_PATH . '/css/magnific-popup.css');
        $asset->addCss(SITE_TEMPLATE_PATH . '/css/popup.css');
        $asset->addCss(SITE_TEMPLATE_PATH . '/css/select2.min.css');
        $asset->addCss(SITE_TEMPLATE_PATH . '/css/style.css');
        $asset->addCss(SITE_TEMPLATE_PATH . '/build/css/main.css');

        $asset->addCss(SITE_TEMPLATE_PATH . '/css/main.css');

        $asset->addJs(SITE_TEMPLATE_PATH . '/build/js/vendor.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/build/js/main.js');

        $asset->addJs(SITE_TEMPLATE_PATH . '/js/underscore-min.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/tinycolor-min.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/popper.min.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/jquery-ui.min.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/bootstrap.min.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/jquery.mCustomScrollbar.min.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/jquery.mousewheel-3.0.6.min.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/jquery.contextMenu.min.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/jquery.mask.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/select2.min.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/moment.min.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/general.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/custom_calendar.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/ajax_load.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/jquery.magnific-popup.js');
        //$asset->addJs(SITE_TEMPLATE_PATH . '/js/popup.js');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/main.js');
    }

    ?>
</head>
<body <?if(!$isNewTemplateVersion):?>data-sessid="<?=bitrix_sessid()?>" data-post-form-action="<?=POST_FORM_ACTION_URI?>"<?endif;?>>

    <?if(!$isNewTemplateVersion):?>
        <div id="panel">
            <?//$APPLICATION->ShowPanel()?>
        </div>

        <?$APPLICATION->IncludeComponent("bitrix:menu","left",Array(
                "ROOT_MENU_TYPE" => "left",
                "MAX_LEVEL" => "1",
                "CHILD_MENU_TYPE" => "left",
                "USE_EXT" => "N",
                "DELAY" => "N",
                "ALLOW_MULTI_SELECT" => "N",
                "MENU_CACHE_TYPE" => "Y",
                "MENU_CACHE_TIME" => "36000",
                "MENU_CACHE_USE_GROUPS" => "N",
            )
        );?>

        <div class="container-fluid" id="body">
            <div class="row main_header">
                <div class="header_clock" data-ts="<?=time()?>000" id="header-clock"><?=date('H:i')?></div>

                <?
                $APPLICATION->IncludeComponent(
                    "newSmile:notice.list",
                    "main",
                    array()
                );
                ?>

                <?/*$APPLICATION->IncludeComponent(
                    "newSmile:search.title",
                    "header",
                    array(
                        'USE_LANGUAGE_GUESS' => 'Y',
                        'MIN_QUERY_LENGTH' => 3,
                        'TOP_COUNT' => 200,
                        'CATEGORIES' => array(
                            'patientcard' => array(
                                'TITLE' => 'Пациенты',
                                'ENTITY' => 'Mmit\NewSmile\PatientCard',
                                'FIELDS' => array('PERSONAL_BIRTHDAY', 'NAME', 'LAST_NAME', 'SECOND_NAME')
                            ),
                            'doctor' => array(
                                'TITLE' => 'Врачи',
                                'ENTITY' => 'Mmit\NewSmile\Doctor',
                                'FIELDS' => array('PERSONAL_BIRTHDAY', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'COLOR')
                            )
                        )
                    )
                );*/
                Application::getInstance()->renderReactComponent('Search', [
                    'useLanguageGuess' => true,
                    'minQueryLength' => 3,
                    'topCount' => 200
                ], 'header-search-root');
                ?>
                <div class="header_place">
                    <div class="place_current">
                        <div class="place_current_city">Иркутск</div>
                        <div class="place_current_adrs">Донская, 24/3</div>
                    </div>
                    <div class="header_drwnarr"></div>
                </div>
                <div class="header_user">
                    <div class="header_user_img statusred">
                        <img src="<?=SITE_TEMPLATE_PATH?>/images/user.jpg"/>
                        <div class="header_user_status"></div>
                    </div>
                    <div class="header_user_data">
                        <div class="header_user_pstn">Администратор</div>
                        <div class="header_user_name">Константинов М.В.</div>
                    </div>
                    <div class="header_drwnarr"></div>
                </div>
            </div>

    <?endif;?>