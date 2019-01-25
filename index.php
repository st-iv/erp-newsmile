<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Главная страница");

use Mmit\NewSmile;
use Mmit\NewSmile\Command;

$application = NewSmile\Application::getInstance();
?>
<?
\Bitrix\Main\Loader::includeModule('mmit.newsmile');
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
        'schedule' => new Command\Schedule\GetDaysInfo(),
        'initialDate' => date('Y-m-d'),

        'doctors' => new Command\Doctor\GetListMobile([
            'select' => ['ID', 'NAME', 'COLOR', 'LAST_NAME', 'SECOND_NAME'],
            'get-specialization' => true
        ])
    ]);
    ?>
<?

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>