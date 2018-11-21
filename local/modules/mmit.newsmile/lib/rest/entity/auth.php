<?

namespace Mmit\NewSmile\Rest\Entity;

use Bitrix\Main\UserTable;
use Mmit\NewSmile\Application;
use Mmit\NewSmile\Config;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Sms;

class Auth extends Controller
{
    protected $testMode = true;


    protected function processSendCode()
    {
        $phone = $this->getParam('phone');

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

    protected function processGetToken()
    {
        $phone = $this->getParam('phone');
        $code = $this->getParam('code');

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

    public function processAuthorize()
    {
        $token = $this->getParam('token');
        $this->authorize($token);
    }

    public function authorize($token, $bGetUserInfo = true)
    {
        $userId = Sms\TokenTable::getUserByToken($token);
        if ($userId)
        {
            if($bGetUserInfo)
            {
                $this->responseData = $this->getUserInfo($userId);;
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

    protected function getActionsMap()
    {
        return [
            'authorize' => [
                'DEFAULT' => true,
                'PARAMS' => [
                    'token' => [
                        'TITLE' => 'токен авторизации'
                    ]
                ]
            ],

            'send-code' => [
                'PARAMS' => [
                    'phone' => [
                        'TITLE' => 'телефон'
                    ]
                ]
            ],

            'get-token' => [
                'PARAMS' => [
                    'phone' => [
                        'TITLE' => 'телефон'
                    ],
                    'code' => [
                        'TITLE' => 'код подтверждения'
                    ]
                ]
            ]
        ];
    }
}