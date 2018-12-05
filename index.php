<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Главная страница");

use Mmit\NewSmile;
use Mmit\NewSmile\Command;

$application = NewSmile\Application::getInstance();
?>
<?
\Bitrix\Main\Loader::includeModule('mmit.newsmile');

$filter = $APPLICATION->IncludeComponent(
    "newSmile:calendar.filter",
    "",
    array()
);

?>
    <?
    $application->renderReactComponent('Schedule', [
        'calendar' => [
            'colorsScheme' => [
                0 => array(
                    'background' => '454545',
                    'text' => 'fff'
                ),
                30 => array(
                    'background' => 'ff3758',
                    'text' => 'fff'
                ),
                90 => array(
                    'background' => 'ffb637',
                    'text' => 'fff'
                ),
                150 => array(
                    'background' => 'eaed14',
                ),
                270 => array(
                    'background' => '73cc00',
                    'text' => 'fff'
                )
            ],
            'data' => new Command\Schedule\GetCalendar()
        ],
        'scheduleDay' => new Command\Schedule\GetDayInfo(),
        'initialDate' => date('Y-m-d')
    ]);
    ?>
<?

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>