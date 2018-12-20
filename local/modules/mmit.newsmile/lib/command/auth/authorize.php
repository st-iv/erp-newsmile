<?

namespace Mmit\NewSmile\Command\Auth;

use Mmit\NewSmile\Sms;
use Mmit\NewSmile\Application;
use Mmit\NewSmile\CommandParam;

class Authorize extends Base
{
    protected static $name = 'Авторизация по токену';

    protected function doExecute()
    {
        $userId = Sms\TokenTable::getUserByToken($this->params['token']);
        if ($userId)
        {
            if($this->params['get_user_info'])
            {
                $this->result = $this->getUserInfo($userId);;
            }

            $GLOBALS['USER']->Authorize($userId);

            if(!Application::getInstance()->getUser()->is('patient'))
            {
                $this->setError('Пользователь не является пациентом', 'USER_IS_NOT_PATIENT');
            }
        }
        else
        {
            $this->setError('Токен недействителен', 'INVALID_TOKEN');
        }
    }

    public function getParamsMap()
    {
        return [
            new CommandParam\String('token', 'токен авторизации'),
            new CommandParam\Bool(
                'get_user_info',
                'флаг запроса краткой информации о пользователе',
                '',
                false,
                true
            )
        ];
    }
}