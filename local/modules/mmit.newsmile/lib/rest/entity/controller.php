<?

namespace Mmit\NewSmile\Rest\Entity;

use Bitrix\Main\Application;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;

/**
 * Отвечает за обработку определенной сущности rest API.
 * Class Controller
 * @package Mmit\NewSmile\Rest\Entity
 */
abstract class Controller
{
    protected $request;

    /**
     * @var Error
     */
    protected $error;
    protected $responseData;
    protected $testMode = false;

    public function __construct($action = '')
    {
        $this->request = Application::getInstance()->getContext()->getRequest();
    }

    /**
     * Проверяет соответствует ли метод запроса указанному, при несоответствии устанавливает ошибку NOT_SUPPORTED_HTTP_METHOD
     * @param $method
     *
     * @return bool
     */
    protected function checkMethod($method)
    {
        if($this->request->getRequestMethod() != $method)
        {
            $errorMessage = 'HTTP метод ' . $this->request->getRequestMethod() . ' не поддерживается для сущности ' . $this->getEntityCode()
                . '. Ожидается метод ' . $method;

            $this->setError('NOT_SUPPORTED_HTTP_METHOD', $errorMessage);

            return false;
        }

        return true;
    }

    /**
     * @return array
     */
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

    public function getEntityCode()
    {
        return strtolower(Helpers::getShortClassName(static::class));
    }

    public function getError()
    {
        return $this->error;
    }

    public function process($action = '')
    {
        if (!$action)
        {
            $action = $this->getDefaultAction();

            if(!$action)
            {
                $this->setError('Операция по умолчанию не определена', 'DEFAULT_ACTION_IS_NOT_DEFINED');
                return;
            }
        }

        if(!$this->checkParams($action))
        {
            return;
        }

        $specificMethodName = 'process' . Helpers::getCamelCase($action);

        if (method_exists($this, $specificMethodName))
        {
            $this->$specificMethodName();
        } else
        {
            $this->setError('Операция ' . $action . ' не поддерживается ', 'ACTION_IS_NOT_SUPPORTED');
        }
    }

    protected function getDefaultAction()
    {
        $defaultAction = '';
        $actionsMap = $this->getActionsMap();

        foreach ($actionsMap as $actionCode => $action)
        {
            if($action['DEFAULT'])
            {
                $defaultAction = $actionCode;
                break;
            }
        }

        return $defaultAction;
    }

    protected function checkParams($action)
    {
        $actionsMap = $this->getActionsMap();
        $params = $actionsMap[$action]['PARAMS'];
        $result = true;

        foreach ($params as $paramCode => $param)
        {
            if(($param['REQUIRED'] !== false) && !isset($this->request[$paramCode]))
            {
                $this->setError('Не указан параметр \'' . $param['TITLE'] . '\' (' . $paramCode . ')', $code = 'ACTION_PARAMETER_NOT_DEFINED');
                $result = false;
                break;
            }
        }

        return $result;
    }

    protected function getParam($paramName)
    {
        $result = null;

        if(isset($this->request[$paramName]))
        {
            $result = strip_tags($this->request[$paramName]);
            $result = htmlspecialchars($result);
        }

        return $result;
    }

    abstract protected function getActionsMap();
}