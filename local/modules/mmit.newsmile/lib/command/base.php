<?


namespace Mmit\NewSmile\Command;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Orm\Helper;

abstract class Base
{
    /**
     * @var Error
     */
    private $error;
    protected $params;
    protected $result;

    protected $varyParam;
    protected $variants;

    protected $isTestMode = false;

    protected static $name = '';

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
            else
            {
                $this->params[$paramCode] = $paramInfo['DEFAULT'];
            }
        }
    }

    protected function checkParams()
    {
        foreach ($this->getParamsMap() as $paramCode => $paramInfo)
        {
            if($paramInfo['REQUIRED'] && !isset($this->params[$paramCode]) && ($paramCode !== $this->varyParam))
            {
                throw new Error(
                    'Не указан обязательный параметр ' . $paramCode . ' для команды ' . $this->getCode(),
                    'REQUIRED_PARAM_NOT_DEFINED'
                );
            }
        }

        return true;
    }

    /**
     * Проверяет, возможно ли выполнение команды текущим пользователем
     * @return bool
     */
    public static function isAvailableForUser()
    {
        $result = false;
        $operations = static::getOperations();

        if(!$operations)
        {
            $result = true;
        }
        else
        {
            $accessController = Application::getInstance()->getAccessController();
            $entityCode = static::getEntityCode();

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
     * Проверяет, можно ли выполнить команду в от имени текущего пользователя в данный момент при указанных параметрах.
     * @return bool
     */
    final public function isAvailable()
    {
        return static::isAvailableForUser() && $this->checkParams() && $this->checkAvailable();
    }

    /**
     * Проверяет, можно ли выполнить команду в данный момент при указанных параметрах
     * @return bool
     */
    protected function checkAvailable()
    {
        return true;
    }

    public function setVaryParam($varyParam)
    {
        $this->varyParam = $varyParam;
    }

    public function getVariants()
    {
        $variants = [];
        foreach ($this->variants as $variantCode => $variantName)
        {
            $variants[] = [
                'code' => $variantCode,
                'name' => $variantName
            ];
        }

        return $variants;
    }


    /**
     * Определяет, частью каких операций является данная команда. Для возможности выполнения команды у пользователя
     * должно быть
     * @return array
     */
    protected static function getOperations()
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

    public static function getCode()
    {
        return static::getEntityCode() . '/' . static::getShortCode();
    }

    protected static function getShortCode()
    {
        return static::getCodeFor(true);
    }

    protected static function getEntityCode()
    {
        return static::getCodeFor(false);
    }

    private static function getCodeFor($bCommand)
    {
        $offset = $bCommand ? -1 : -2;
        $code = '';

        if(preg_match_all('/\\\\([A-Za-z0-9]+)/', static::class, $matches))
        {
            $code = Helpers::getSnakeCase(array_slice($matches[1], $offset, 1)[0], false, '-');
        }

        return $code;
    }

    public static function getClassByCode($code)
    {
        $code = explode('/', $code);
        array_walk($code, function(&$codePart)
        {
            $codePart = Helpers::getCamelCase($codePart);
        });

        $relClassName = implode('\\', $code);
        return __NAMESPACE__ . '\\' . $relClassName;
    }

    /**
     * Возвращает название команды
     * @return string
     */
    public static function getName()
    {
        return static::$name;
    }

    final public function execute()
    {
        if($this->isAvailable())
        {
            $this->doExecute();
        }
    }


    /**
     * Выполняет команду
     * @return mixed
     */
    abstract protected function doExecute();

    /**
     * Возвращает описание параметров команды
     * @return mixed
     */
    abstract public function getParamsMap();
}