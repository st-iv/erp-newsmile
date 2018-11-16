<?

namespace Mmit\NewSmile\Rest\Operation;

use Bitrix\Main\Application;
use Mmit\NewSmile\Error;

abstract class Controller
{
    protected $request;

    /**
     * @var Error
     */
    protected $error;
    protected $responseData;
    protected $testMode = false;
    protected $operation;

    public function __construct($operation)
    {
        $this->operation = $operation;
        $this->request = Application::getInstance()->getContext()->getRequest();
    }

    protected function checkMethod($method)
    {
        if($this->request->getRequestMethod() != $method)
        {
            $errorMessage = 'HTTP метод ' . $this->request->getRequestMethod() . ' не поддерживается для операции ' . $this->request['operation']
                . '. Ожидается метод ' . $method;

            $this->setError('NOT_SUPPORTED_HTTP_METHOD', $errorMessage);

            return false;
        }

        return true;
    }

    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * @param string|Error $message
     * @param string $code
     */
    protected function setError($message, $code = '')
    {
        if(!$code && ($message instanceof Error))
        {
            $this->error = $message;
        }
        elseif($message && $code)
        {
            $this->error = new Error($message, $code);
        }
    }

    public function getError()
    {
        return $this->error;
    }

    abstract public function process();
}