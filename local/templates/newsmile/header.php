<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?$asset = \Bitrix\Main\Page\Asset::getInstance();?>
<!DOCTYPE html>
<html lang="<?=LANGUAGE_ID?>">
<head>
    <title><?$APPLICATION->ShowTitle()?></title>
    <?$APPLICATION->ShowHead()?>

    <?
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/style.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/bootstrap.min.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/jquery.mCustomScrollbar.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/jquery-ui.min.css');

    $asset->addJs(SITE_TEMPLATE_PATH . '/js/jquery-3.3.1.min.js');
    $asset->addJs(SITE_TEMPLATE_PATH . '/js/popper.min.js');
    $asset->addJs(SITE_TEMPLATE_PATH . '/js/jquery-ui.min.js');
    $asset->addJs(SITE_TEMPLATE_PATH . '/js/bootstrap.min.js');
    $asset->addJs(SITE_TEMPLATE_PATH . '/js/jquery.mCustomScrollbar.min.js');
    $asset->addJs(SITE_TEMPLATE_PATH . '/js/jquery.mousewheel-3.0.6.min.js');
    $asset->addJs(SITE_TEMPLATE_PATH . '/js/moment.min.js');
    $asset->addJs(SITE_TEMPLATE_PATH . '/js/custom_calendar.js');
    $asset->addJs(SITE_TEMPLATE_PATH . '/js/main.js');
    ?>
</head>
<body>
    <div id="panel">
        <?$APPLICATION->ShowPanel()?>
    </div>

    <div class="left_menu">
        <div class="menu_btn_shld"></div>
        <div class="menu_btn_kartoteka"></div>
        <div class="menu_btn_options"></div>
    </div>
    <div class="left_menu_vline"></div>
    <div class="left_menu_content">
        <div class="menu_shld_itmslst">
            <div class="menu_shld_item">Запланировать прием</div>
            <div class="menu_shld_item">Лист ожидания</div>
            <div class="menu_shld_item">Новая запись в лист ожидания</div>
            <div class="menu_shld_item">Рассылки</div>
            <div class="menu_shld_item">Отправить SMS</div>
            <div class="menu_shld_item">Журнал заказ-нарядов</div>
        </div>
    </div>
    <div class="notif_content">
        <div class="notif_header">Уведомления</div>
        <div class="notif_content_close"></div>
        <div class="notif_tabs">
            <div class="notif_tab tActive" data-select="all">Все</div>
            <div class="notif_tab" data-select="pr">Приёмы</div>
            <div class="notif_tab" data-select="sys">Системные</div>
            <div class="notif_tab" data-select="ch">Начальство</div>
        </div>
        <div class="notif_items">
            <div class="notif_item" data-type="pr">
                <div class="notif_data">
                    <div class="notif_data_date">Сегодня, в 12:30</div>
                    <div class="notif_data_action notifClose">Завершить</div>
                </div>
                <div class="notif_status statusCl">Прием закончился</div>
                <div class="notif_user">
                    <div class="notif_user_name">Степанов Валентин<br/>Алексеевич – 45 лет</div>
                    <div class="notif_user_doctor" style="background-color: #713fd6;">Виноградова И.Б.</div>
                </div>
                <div class="notif_text">Здесь будет расположен текст того, что должен сделать администратор следующим шагом, после того как прием закончен.</div>
                <div class="notif_close"></div>
            </div>
            <div class="notif_item" data-type="sys">
                <div class="notif_data">
                    <div class="notif_data_date">Сегодня, в 12:30</div>
                    <div class="notif_data_action">Подробнее</div>
                </div>
                <div class="notif_status statusReject">Документы не прошли проверку</div>
                <div class="notif_text">Текст уведомления, текст уведомления, текст уведомления, текст уведомления, текст уведомления, текст уведомления, текст уведомления</div>
                <div class="notif_close"></div>
            </div>
            <div class="notif_item" data-type="pr">
                <div class="notif_data">
                    <div class="notif_data_date">Сегодня, в 12:30</div>
                    <div class="notif_data_action notifClose">Завершить</div>
                </div>
                <div class="notif_status statusCl">Прием закончился</div>
                <div class="notif_user">
                    <div class="notif_user_name">Степанов Валентин<br/>Алексеевич – 45 лет</div>
                    <div class="notif_user_doctor" style="background-color: #ffba61;">Груничев В.А.</div>
                </div>
                <div class="notif_text">Текст уведомления, текст уведомления, текст уведомления, текст уведомления, текст уведомления, текст уведомления, текст уведомления</div>
                <div class="notif_close"></div>
            </div>
            <div class="notif_item" data-type="sys">
                <div class="notif_data">
                    <div class="notif_data_date">Сегодня, в 11:47</div>
                    <div class="notif_data_action">Подробнее</div>
                </div>
                <div class="notif_status statusReject">Документы не прошли проверку</div>
                <div class="notif_text">Текст уведомления, текст уведомления, текст уведомления, текст уведомления, текст уведомления, текст уведомления, текст уведомления</div>
                <div class="notif_close"></div>
            </div>
            <div class="notif_item" data-type="ch">
                <div class="notif_data">
                    <div class="notif_data_date">Сегодня, в 11:47</div>
                    <div class="notif_data_action">Подробнее</div>
                </div>
                <div class="notif_status statusReject">Начальство недовольно</div>
                <div class="notif_text">Текст уведомления, текст уведомления, текст уведомления, текст уведомления, текст уведомления, текст уведомления, текст уведомления</div>
                <div class="notif_close"></div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row main_header">
            <div class="header_clock">12:40</div>
            <div class="header_notif">
                <div class="notif_bell">
                    <div class="notif_amnt">14</div>
                </div>
            </div>
            <form class="header_search_form">
                <input type="text" name="search_str" class="search_str" placeholder="Искать пациента, врача, документ" />
                <div class="search_sbmt"></div>
            </form>
            <div class="header_place">
                <div class="place_current">
                    <div class="place_current_city">Иркутск</div>
                    <div class="place_current_adrs">Донская, 24/3</div>
                </div>
                <div class="header_drwnarr"></div>
            </div>
            <div class="header_user">
                <div class="header_user_img statusred">
                    <img src="images/user.jpg"/>
                    <div class="header_user_status"></div>
                </div>
                <div class="header_user_data">
                    <div class="header_user_pstn">Администратор</div>
                    <div class="header_user_name">Константинов М.В.</div>
                </div>
                <div class="header_drwnarr"></div>
            </div>
        </div>

