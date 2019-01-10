<?

namespace Mmit\NewSmile\Command\Notice;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\CommandParam;

class SetFirebaseToken extends Base
{
    protected function doExecute()
    {
        $user = Application::getInstance()->getUser();

        $userInfo = $user->getBitrixUserFields(['UF_FIREBASE_TOKEN']);
        $firebaseTokens = (is_array($userInfo['UF_FIREBASE_TOKEN']) ? $userInfo['UF_FIREBASE_TOKEN'] : []);

        if(!in_array($this->params['firebase_token'], $firebaseTokens))
        {
            $firebaseTokens[] = $this->params['firebase_token'];

            $user->setBitrixUserFields([
                'UF_FIREBASE_TOKEN' => $firebaseTokens
            ]);
        }
    }

    public function getParamsMap()
    {
        return [
            new CommandParam\String(
                'firebase_token',
                'firebase токен',
                'Токен firebase конкретного экземпляра мобильного приложения',
                true
            )
        ];
    }
}