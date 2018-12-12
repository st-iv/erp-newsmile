<?
use Mmit\NewSmile\Command;

$command = new Command\Schedule\GetDayInfo([
    'date' => '11.12.2018'
]);

$command->execute();
$result = $command->getResult();


