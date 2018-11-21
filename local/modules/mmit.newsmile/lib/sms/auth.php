<?php

namespace Mmit\NewSmile\Sms;

use Mmit\NewSmile\Config,
    Bitrix\Main\SiteTable,
    Bitrix\Main\UserTable;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Sms\Transport;
use Mmit\NewSmile\Exception;

class Auth
{
    const CODE = 'UF_CONFIRMATION_CODE';
    const CODE_EXPIRATION = 'UF_CODE_EXPIRATION';

    private $userId;
    private $phone;
    private $bCodeDropped = false;
    private $bExpirationExceeded = false;
    private $codeExpiration = 0;
    private $isTestMode = false;

    /**
     * @var Error
     */
    private $error;

    public function __construct($phone)
    {
        $this->userId = static::getIdByPhone($phone);
        $this->phone = $phone;

        if(!$this->userId)
        {
            throw new Error('Пользователь с телефоном ' . $phone . ' не найден', 'USER_NOT_FOUND');
        }
    }

    public function dropCode()
    {
        $this->bCodeDropped = true;
        $this->setCode('', 0);
        $_SESSION['phone_auth_wrong_attempts'] = 0;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public static function generateCode()
    {
        $chars = '0123456789';
        $numChars = strlen($chars);
        $string = '';
        $codeLength = Config::getOption('sms_auth_code_length');

        for ($i = 0; $i < $codeLength; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $string;
    }

    public function getCodeExpiration()
    {
        if(!$this->codeExpiration)
        {
            $dbUser = \CUser::GetList(
                $sortBy,
                $sortOrder,
                array(
                    'ID' => $this->userId
                ),
                array(
                    'SELECT' => array(
                        self::CODE_EXPIRATION
                    )
                )
            );

            if($arUser = $dbUser->Fetch())
            {
                $this->codeExpiration = $arUser[self::CODE_EXPIRATION];
            }
        }

        return $this->codeExpiration;
    }

    private static function getIdByPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if(!$phone)
        {
            return 0;
        }

        $dbUsers = UserTable::getList(array(
            'filter' => array(
                'PERSONAL_PHONE' => $phone
            ),
            'select' => array('ID')
        ));

        if($arUser = $dbUsers->fetch())
        {
            return $arUser['ID'];
        }
        else
        {
            return 0;
        }
    }

    /**
     * @return \DateTime
     */
    public static function getSmsBlockExceedTime()
    {
        $obTime = new \DateTime();
        $obTime->setTimestamp($_SESSION['phone_auth_last_sms_time']);
        $obTime->modify('+' . Config::getOption('sms_auth_block_duration') . ' minute');

        return $obTime;
    }

    public function isCodeCorrect($code)
    {
        $sortBy = 'id';
        $sortOrder = 'asc';

        $dbUser = \CUser::GetList(
            $sortBy,
            $sortOrder,
            array(
                'ID' => $this->userId
            ),
            array(
                'SELECT' => array(
                    self::CODE,
                    self::CODE_EXPIRATION
                )
            )
        );

        if($arUser = $dbUser->Fetch())
        {
            $bCodeCorrect = ( (time() < $arUser[self::CODE_EXPIRATION]) && ($code == $arUser[self::CODE]) );
            if($bCodeCorrect)
            {
                $this->dropCode();
            }
            else
            {
                if(time() >= $arUser[self::CODE_EXPIRATION])
                {
                    $this->dropCode();
                    $this->bExpirationExceeded = true;
                }
                else
                {
                    $wrongAttempts = intval($_SESSION['phone_auth_wrong_attempts']);
                    $wrongAttempts++;

                    if($wrongAttempts == Config::getOption('sms_auth_attempt_limit'))
                    {
                        //если было совершено слишком много неудачных попыток ввода пароля - сбрасываем пароль
                        $this->dropCode();
                    }
                    else
                    {
                        $_SESSION['phone_auth_wrong_attempts'] = $wrongAttempts;
                    }

                }

            }

            $this->codeExpiration = (($this->bCodeDropped) ? 0 : $arUser[self::CODE_EXPIRATION]);

            return $bCodeCorrect;
        }
        else
        {
            return false;
        }
    }

    public function isCodeDropped()
    {
        return $this->bCodeDropped;
    }

    public function isExpirationExceeded()
    {
        return $this->bExpirationExceeded;
    }

    public static function isSmsLimitExceeded()
    {
        return (($_SESSION['phone_auth_sms_count'] == Config::getOption('sms_auth_sms_limit'))
                        && (self::getSmsBlockExceedTime()->getTimestamp() > time()));
    }

    public function sendNewCode()
    {
        //сброс счетчика смс, если последняя смс было отправлено достаточно давно
        if((time() - $_SESSION['phone_auth_last_sms_time']) > (Config::getOption('sms_auth_sms_limit_duration') * 60))
        {
            $_SESSION['phone_auth_sms_count'] = 0;
        }


        /* проверка дотигнуто ли ограничение смс */

        $bSmsLimitReached = ($_SESSION['phone_auth_sms_count'] >= Config::getOption('sms_auth_sms_limit'));
        $bSmsBlockEnabled = ((time() - $_SESSION['phone_auth_last_sms_time']) < (Config::getOption('sms_auth_block_duration') * 60));


        if($bSmsLimitReached)
        {
            if($bSmsBlockEnabled)
            {
                throw new Error('Отправка СМС временно заблокирована в связи с превышением ограничения отправленных смс', 'SMS_AUTH_BLOCKED');
            }
            else
            {
                $_SESSION['phone_auth_sms_count'] = 0;
            }
        }

        /* генерация сообщения, сохранение кода */

        $code = self::generateCode();
        self::setCode($code, Config::getOption('sms_auth_code_expiration'));

        $codesInsertionCount = 0;
        $text = str_replace('#CODE#', $code, Config::getOption('sms_auth_message'), $codesInsertionCount);

        if(!$codesInsertionCount)
        {
            $text = $code;
        }


        /* отправка сообщения */

        if($this->isTestMode)
        {
            // в режиме тестирования отправка сообщения не выполняется, вместо этого притворяемся, что отправка прошла успешно
            $arSendingResult = array(
                'code' => 1
            );
        }
        else
        {
            $arMessage = array(
                'text' => $text,
                'source' => Config::getOption('sms_auth_sender')
            );

            $obSmsTransport = new Transport();
            $arSendingResult = $obSmsTransport->send($arMessage, $this->phone);
        }

        if($arSendingResult['code'] === 1)
        {
            $_SESSION['phone_auth_sms_count'] = intval($_SESSION['phone_auth_sms_count']) + 1;
            $_SESSION['phone_auth_last_sms_time'] = time();
        }
        else
        {
            throw new Error('Ошибка отправки сообщения, код ошибки: ' . $arSendingResult['code'], 'SEND_MESSAGE_FAILED');
        }

        return $code;
    }

    private function setCode($code, $expiration)
    {
        $obUser = new \CUser();
        $expiration = time() + $expiration;

        $obUser->Update($this->userId, array(
            self::CODE => $code,
            self::CODE_EXPIRATION => $expiration
        ));

        $this->codeExpiration = $expiration;
    }

    public function setTestMode($isTestMode)
    {
        $this->isTestMode = $isTestMode;
    }

    protected function setError($message, $code)
    {
        $this->errorMessage = $message;
        $this->errorCode = $code;
    }

    protected function getErrorCode()
    {
        return $this->errorCode;
    }

    protected function getErrorMessage()
    {
        return $this->errorMessage;
    }
}