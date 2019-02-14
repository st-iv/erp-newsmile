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

<div id="root"></div>
<?

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>