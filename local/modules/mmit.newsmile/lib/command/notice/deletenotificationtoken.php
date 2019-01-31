<?

namespace Mmit\NewSmile\Command\Notice;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandParam;
use Mmit\NewSmile\Notice;

class DeleteNotificationToken extends Base
{
    protected function doExecute()
    {
        $user = Application::getInstance()->getUser();

        $userInfo = $user->getBitrixUserFields(['UF_FIREBASE_TOKEN']);
        $firebaseTokens = (is_array($userInfo['UF_FIREBASE_TOKEN']) ? $userInfo['UF_FIREBASE_TOKEN'] : []);
        $bUpdate = false;

        foreach ($firebaseTokens as $tokenIndex => $token)
        {
            $tokenParts = Notice\Helper::getNotificationTokenInfo($token);
            if($tokenParts['CLEAN_TOKEN'] == $this->params['notification_token'])
            {
                unset($firebaseTokens[$tokenIndex]);
                $bUpdate = true;
                break;
            }
        }

        if($bUpdate)
        {
            $user->setBitrixUserFields([
                'UF_FIREBASE_TOKEN' => $firebaseTokens
            ]);
        }
    }


    public function getParamsMap()
    {
        return [
            new \Mmit\NewSmile\CommandVariable\String('notification_token', 'токен для получения уведомлений', true
            )
        ];
    }
}