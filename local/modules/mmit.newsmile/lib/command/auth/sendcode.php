<?

namespace Mmit\NewSmile\Command\Auth;

use Mmit\NewSmile\CommandVariable\String;
use Mmit\NewSmile\Sms;
use Mmit\NewSmile\Error;

class SendCode extends Base
{
    protected $isTestMode = true;

    public function getDescription()
    {
        return 'Отправляет смс код подтверждения на указанный номер';
    }

    protected function doExecute()
    {
        try
        {
            $smsAuth = new Sms\Auth($this->params['phone']);
            $smsAuth->setTestMode($this->isTestMode);

            $code = $smsAuth->sendNewCode();

            if($this->isTestMode)
            {
                $this->result['code'] = $code;
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
            new String('phone', 'телефон', true)
        ];
    }
}