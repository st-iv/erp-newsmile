<?

namespace Mmit\NewSmile\Rest\Operation;

use Bitrix\Main\UserTable;
use Mmit\NewSmile\Config;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Exception;
use Mmit\NewSmile\Sms;

class Auth extends Controller
{
    protected $testMode = true;

    public function process()
    {
        if(!$this->checkMethod('GET'))
        {
            return;
        }

        if ($this->request['phone'] && !$this->request['code'])
        {
            $this->sendNewCode($this->request['phone']);
        }
        elseif($this->request['phone'] && $this->request['code'])
        {
            $this->generateToken($this->request['phone'], $this->request['code']);
        }
        elseif(!empty($this->request['token']))
        {
            $this->authorize($this->request['token']);
        }
        else
        {
            $this->setError('Не указаны параметры, необходимые для выполнения операции ' . $this->operation, 'PARAMS_NOT_SPECIFIED');
        }
    }

    protected function sendNewCode($phone)
    {
        $phone = strip_tags($phone);
        $phone = htmlspecialchars($phone);

        try
        {
            $smsAuth = new Sms\Auth($phone);
            $smsAuth->setTestMode($this->testMode);

            $code = $smsAuth->sendNewCode();

            if($this->testMode)
            {
                $this->responseData['code'] = $code;
            }

        }
        catch(Error $e)
        {
            $this->setError($e);
        }
    }

    protected function generateToken($phone, $code)
    {
        $phone = strip_tags($phone);
        $phone = htmlspecialchars($phone);

        $code = strip_tags($code);
        $code = htmlspecialchars($code);

        try
        {
            $smsAuth = new Sms\Auth($phone);
            if($smsAuth->isCodeCorrect($code))
            {
                $userId = $smsAuth->getUserId();
                $token = Sms\TokenTable::createToken($userId);

                if ($token)
                {
                    $this->responseData = $this->getUserInfo($userId);
                    $this->responseData['token'] = $token;
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

    protected function authorize($token)
    {
        $token = strip_tags($token);
        $token = htmlspecialchars($token);

        $userId = Sms\TokenTable::getUserByToken($token);
        if ($userId)
        {
            $this->responseData = $this->getUserInfo($userId);
        }
        else
        {
            $this->setError('Токен недействителен', 'INVALID_TOKEN');
        }
    }

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