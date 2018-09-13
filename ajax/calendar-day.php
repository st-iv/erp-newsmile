<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 23.08.2018
 * Time: 12:07
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
?>
<?$APPLICATION->IncludeComponent(
    "newSmile:calendar.day",
    "",
    [
        'FILTER_NAME' => 'arrFilterCalendar'
    ]
);?>