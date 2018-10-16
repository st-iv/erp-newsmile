<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Mmit\NewSmile\Date;
use Mmit\NewSmile;

$arResult['UNREAD_COUNT'] = 0;

foreach ($arResult['NOTICES'] as &$notice)
{
    $notice['TIME_FORMATTED'] = Date\Helper::formatDateTime($notice['TIME']);

    if($notice['TYPE'] == 'VISIT_FINISHED')
    {
        $notice['PARAMS']['DOCTOR_FIO'] = NewSmile\Helpers::getFio($notice['PARAMS'], 'DOCTOR_');
    }

    $notice['TITLE_ICON_CLASS'] = '';

    switch ($notice['TYPE'])
    {
        case 'VISIT_FINISHED':
            $notice['TITLE_CLASS'] = 'Cl';
            break;

        case 'BAD_DOCUMENTS':
        case 'BOSSES_UNHAPPY':
            $notice['TITLE_CLASS'] = 'Reject';
            break;
    }

    if(!$notice['IS_READ'])
    {
        $arResult['UNREAD_COUNT']++;
    }
}

unset($notice);
