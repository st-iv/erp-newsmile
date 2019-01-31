<?

namespace Mmit\NewSmile\Command\Auth;

use Mmit\NewSmile\Sms;
use Mmit\NewSmile\Application;
use Mmit\NewSmile\CommandVariable;
use Mmit\NewSmile\Command;

class Authorize extends Base
{
    public function getDescription()
    {
        return 'Авторизует пользователя в системе по токену, возвращает информацию по авторизованному пользователю';
    }

    public function getResultFormat()
    {
        return new Command\ResultFormat([
            new CommandVariable\String('name', 'имя пациента', false),
            new CommandVariable\String('lastName', 'фамилия пациента', false),
            new CommandVariable\String('secondName', 'отчество пациента', false),
        ]);
    }

    protected function doExecute()
    {
        $userId = Sms\TokenTable::getUserByToken($this->params['token']);
        if ($userId)
        {
            if($this->params['get_user_info'])
            {
                $this->result = $this->getUserInfo($userId);
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
            new CommandVariable\String('token', 'токен авторизации'),
            new CommandVariable\Bool('get_user_info', 'флаг запроса краткой информации о пользователе', false, true
            )
        ];
    }
}