<?


namespace Mmit\NewSmile\Command;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;

abstract class Base
{
    /**
     * @var Error
     */
    private $error;
    protected $params;
    protected $result;

    protected $isTestMode = false;

    public function __construct($params = [])
    {
        $this->setParams($params);
    }

    protected function setParams($params)
    {
        foreach ($this->getParamsMap() as $paramCode => $paramInfo)
        {
            if(isset($params[$paramCode]))
            {
                $this->params[$paramCode] = $params[$paramCode];
            }
            elseif($paramInfo['REQUIRED'])
            {
                throw new Error(
                    'Не указан обязательный параметр ' . $paramCode . ' для команды ' . $this->getCode(),
                    'REQUIRED_PARAM_NOT_DEFINED'
                );
            }
            else
            {
                $this->params[$paramCode] = $paramInfo['DEFAULT'];
            }
        }
    }

    /**
     * Проверяет, возможно ли выполнение команды текущим пользователем на данный момент
     * @return bool
     */
    protected function isAvailable()
    {
        $result = false;
        $operations = $this->getOperations();

        if(!$operations)
        {
            $result = true;
        }
        else
        {
            $accessController = Application::getInstance()->getAccessController();
            $entityCode = $this->getEntityCode();

            foreach ($operations as $operationCode)
            {
                if($accessController->isOperationAllowed($entityCode, $operationCode))
                {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Определяет, частью каких операций является данная команда. Для возможности выполнения команды у пользователя
     * должно быть
     * @return array
     */
    protected function getOperations()
    {
        return [];
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

    public function getResult()
    {
        return $this->result;
    }

    protected function getCode()
    {
        return $this->getEntityCode() . ':' . $this->getShortCode();
    }

    protected function getShortCode()
    {
        return $this->getCodeFor(true);
    }

    protected function getEntityCode()
    {
        return $this->getCodeFor(false);
    }

    private function getCodeFor($bCommand)
    {
        $offset = $bCommand ? -1 : -2;
        $code = '';

        if(preg_match_all('/\\\\([A-Za-z0-9]+)/', static::class, $matches))
        {
            $code = Helpers::getSnakeCase(array_slice($matches[1], $offset, 1)[0], false, '-');
        }

        return $code;
    }

    /**
     * Выполняет команду
     * @return mixed
     */
    abstract public function execute();

    /**
     * Возвращает описание параметров команды
     * @return mixed
     */
    abstract public function getParamsMap();

    /**
     * Возвращает название команды
     * @return string
     */
    abstract public function getName();
}