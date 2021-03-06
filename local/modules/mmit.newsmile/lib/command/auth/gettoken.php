<?

namespace Mmit\NewSmile\Command\Auth;

use Mmit\NewSmile\CommandVariable\String;
use Mmit\NewSmile\Sms,
    Mmit\NewSmile\Config,
    Mmit\NewSmile\Error,
    Mmit\NewSmile\Command,
    Mmit\NewSmile\CommandVariable;

class GetToken extends Base
{
    public function getDescription()
    {
        return 'Возвращает авторизационный токен для пользователя с указанным телефоном, если указан правильный код подтверждения.';
    }

    public function getResultFormat()
    {
        return new Command\ResultFormat([
            new String('token', 'авторизационный токен', true),
            new String('name', 'имя пользователя', true),
            new String('lastName', 'фамилия пользователя', true),
            new String('secondName', 'отчество пользователя', true),
        ]);
    }

    protected function doExecute()
    {
        $phone = $this->params['phone'];
        $code = $this->params['code'];

        try
        {
            $smsAuth = new Sms\Auth($phone);
            if($smsAuth->isCodeCorrect($code))
            {
                $userId = $smsAuth->getUserId();
                $token = Sms\TokenTable::getToken($userId);

                if ($token)
                {
                    $this->result = $this->getUserInfo($userId);
                    $this->result['token'] = $token;
                }
                else
                {
                    $this->setError('Ошибка генерации токена', 'TOKEN_GENERATE_ERROR');
                }
            }
            else
            {
                // определяем причину, по которой код не был принят
                if($smsAuth->isExpirationExceeded())
                {
                    $this->setError('Время жизни кода истекло', 'EXPIRATION_EXCEEDED');
                }
                else
                {
                    $errorMessage = 'Код подтверждения указан неверно';

                    // при определённом количестве неудачных попыток код сбрасывается. Определяем, был ли сброшен код
                    if($smsAuth->isCodeDropped())
                    {
                        // если код был сброшен, отправляем пользователя на этап отправки нового кода
                        $errorMessage .= '. Код обнулен, потому что был неправильно введен ' . Config::getOption('sms_auth_attempt_limit') . ' раз';
                    }

                    $this->setError($errorMessage, 'WRONG_CODE');
                }

            }
        }
        catch(Error $e)
        {
            $this->setError($e);
        }
    }

    public function getParamsMap()
    {
        return [
            new String('phone', 'телефон', true),
            new String('code', 'код подтверждения', true),
        ];
    }
}