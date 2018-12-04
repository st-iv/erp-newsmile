<?

namespace Mmit\NewSmile\Command\Auth;

use Mmit\NewSmile\Sms;
use Mmit\NewSmile\Error;

class SendCode extends Base
{
    protected $isTestMode = true;
    protected static $name = 'Отправить смс-код';

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
            'phone' => [
                'TITLE' => 'телефон',
                'REQUIRED' => true
            ]
        ];
    }
}