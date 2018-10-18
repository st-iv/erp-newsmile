<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$calendar = array();
$currentMonday = strtotime('Monday this week', time());

for ($w = 0; $w < 5; $w++) {
    for ($d = 0; $d < 7; $d++) {
        $difference = mktime(0,0,0,0,$d + $w * 7,0) - mktime(0,0,0,0,0,0);
        $calendar[$w][$d] = date('Y-m-d', $currentMonday + $difference );
    }
}

$arResult['FIELDS']['DATE']['CALENDAR'] = $calendar;