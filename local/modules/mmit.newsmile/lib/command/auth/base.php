<?

namespace Mmit\NewSmile\Command\Auth;

use Mmit\NewSmile\Command;
use Bitrix\Main\UserTable;

abstract class Base extends Command\Base
{
    protected function getUserInfo($userId)
    {
        $user = UserTable::getByPrimary($userId, [
            'select' => ['LAST_NAME', 'NAME', 'SECOND_NAME']
        ])->fetch();

        return [
            'name' => $user['NAME'],
            'last_name' => $user['LAST_NAME'],
            'second_name' => $user['SECOND_NAME']
        ];
    }
}