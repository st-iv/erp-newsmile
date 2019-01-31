<?

namespace Mmit\NewSmile\Command\Notice;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandParam;

class SetNotificationToken extends Base
{
    protected function doExecute()
    {
        $user = Application::getInstance()->getUser();

        $userInfo = $user->getBitrixUserFields(['UF_FIREBASE_TOKEN']);
        $firebaseTokens = (is_array($userInfo['UF_FIREBASE_TOKEN']) ? $userInfo['UF_FIREBASE_TOKEN'] : []);

        if(!in_array($this->params['notification_token'], $firebaseTokens))
        {
            $firebaseTokens[] = $this->params['device'] . '#' . $this->params['notification_token'];

            $user->setBitrixUserFields([
                'UF_FIREBASE_TOKEN' => $firebaseTokens
            ]);
        }
    }

    public function getParamsMap()
    {
        return [
            new \Mmit\NewSmile\CommandVariable\String('notification_token', 'токен для получения уведомлений', true
            ),
            (new \Mmit\NewSmile\CommandVariable\Enum('device', 'тип устройства', true
            ))->setVariants(['android', 'ios'])
        ];
    }
}