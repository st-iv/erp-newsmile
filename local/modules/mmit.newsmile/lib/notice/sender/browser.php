<?


namespace Mmit\NewSmile\Notice\Sender;

use Bitrix\Main\Loader;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\User;

class Browser implements Sender
{
    public function send(array $noticeData, User $user)
    {
        if(!Loader::includeModule('pull')) return;

        \CPullStack::AddByUser($user->getBitrixId(), array(
            'module_id' => 'mmit.newsmile',
            'command' => 'add_notice',
            'params' => Helpers::camelCaseKeys($noticeData, false)
        ));
    }
}