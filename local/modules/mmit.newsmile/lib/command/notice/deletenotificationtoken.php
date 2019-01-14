<?

namespace Mmit\NewSmile\Command\Notice;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandParam;

class DeleteNotificationToken extends Base
{
    protected function doExecute()
    {
        $user = Application::getInstance()->getUser();

        $userInfo = $user->getBitrixUserFields(['UF_FIREBASE_TOKEN']);
        $firebaseTokens = (is_array($userInfo['UF_FIREBASE_TOKEN']) ? $userInfo['UF_FIREBASE_TOKEN'] : []);
        $tokenIndex = array_search($this->params['notification_token'], $firebaseTokens);

        if($tokenIndex !== false)
        {
            unset($firebaseTokens[$tokenIndex]);

            $user->setBitrixUserFields([
                'UF_FIREBASE_TOKEN' => $firebaseTokens
            ]);
        }
    }


    public function getParamsMap()
    {
        return [
            new CommandParam\String(
                'notification_token',
                'токен для получения уведомлений',
                'Токен конкретного экземпляра мобильного приложения',
                true
            )
        ];
    }
}