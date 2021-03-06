<?


namespace Mmit\NewSmile\Command;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\DeleteResult;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\ORM\Data\Result;
use Mmit\NewSmile\Application;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;

abstract class Base
{
    /**
     * @var Error
     */
    private $error;
    protected $params = [];
    protected $result;

    protected $varyParam;
    protected $variants = [];

    protected $isTestMode = false;

    private $isReflectionMode;

    public function __construct($params = [], $varyParam = null, $isReflectionMode = false)
    {
        $this->varyParam = $varyParam;
        $this->isReflectionMode = $isReflectionMode;
        if(!$isReflectionMode)
        {
            $this->setParams($params);
        }
    }

    protected function setParams($params)
    {
        foreach (static::getParamsMap() as $param)
        {
            /**
             * @var \Mmit\NewSmile\CommandVariable\Base $param
             */
            
            $paramCode = $param->getCode();
            $param->setCommand($this);
            
            if($paramCode != $this->varyParam)
            {
                $param->setDefaultEntityCode(static::getEntityCode());
                $param->setRawValue($params[$paramCode]);
                $formattedValue = $param->getFormattedValue();

                if(isset($formattedValue))
                {
                    $this->params[$paramCode] = $this->prepareParamValue($paramCode, $formattedValue);
                }
            }
        }
    }

    /**
     * Обрабатывает значение параметра команды перед сохранением в массив params
     * @param $paramCode
     * @param $paramValue
     *
     * @return mixed
     */
    protected function prepareParamValue($paramCode, $paramValue)
    {
        return $paramValue;
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
        if($this->isReflectionMode)
        {
            $this->sayReflectionModeDisapproves();
        }

        return static::isAvailableForUser() && $this->checkAvailable();
    }

    /**
     * Проверяет, можно ли выполнить команду в данный момент при указанных параметрах. При установленном поле varyParam
     * @return bool
     */
    protected function checkAvailable()
    {
        return true;
    }

    /**
     * Объявляет вариативным параметр с указанным кодом. В зависимости от того, какой параметр объявлен вариативным, формируется
     * список вариантов выполнения команды.
     *
     * @param $varyParam - код параметра
     */
    public function setVaryParam($varyParam)
    {
        $this->varyParam = $varyParam;
    }

    /**
     * Получает варианты выполнения команды в зависимости от того, какой параметр объявлен вариативным через метод setVaryParam
     * @return array
     */
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
     * должно быть ...
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

    public static function getShortCode()
    {
        return static::getCodeFor(true);
    }

    public static function getEntityCode()
    {
        return static::getCodeFor(false);
    }

    public static function getNamespace()
    {
        return __NAMESPACE__;
    }

    public static function getBaseCommandsPath()
    {
        return __DIR__;
    }

    private static function getCodeFor($bCommand)
    {
        $offset = $bCommand ? -1 : -2;
        $code = '';

        if(preg_match_all('/\\\\([A-Za-z0-9]+)/', static::class, $matches))
        {
            $rawCode = array_slice($matches[1], $offset, 1)[0];
            if($bCommand)
            {
                $code = Helpers::getSnakeCase($rawCode, false, '-');
            }
            else
            {
                $code = strtolower($rawCode);
            }
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
    public function getName()
    {
        return '';
    }

    /**
     * Выполняет команду, если её выполнение доступно при текущих условиях
     */
    final public function execute()
    {
        if($this->isAvailable())
        {
            $this->doExecute();
        }
    }

    private function sayReflectionModeDisapproves()
    {
        throw new Error('В режиме reflection недоступно полноценное использование команд (' . static::getCode() . ')');
    }

    /**
     * Проверяет результат операции с ORM сущностью и вывыводит ошибки, если таковые имеются
     * @param Result $result
     * @param string $entityTitle
     *
     * @throws Error
     */
    protected function tellAboutOrmResult(Result $result, $entityTitle = 'записи')
    {
        if(!$result->isSuccess())
        {
            $operationType = null;
            $errorCode = null;

            if($result instanceof AddResult)
            {
                $operationType = 'добавления';
                $errorCode = 'ORM_ADD_ERROR';
            }
            elseif ($result instanceof UpdateResult)
            {
                $operationType = 'обновления';
                $errorCode = 'ORM_UPDATE_ERROR';
            }
            elseif ($result instanceof DeleteResult)
            {
                $operationType = 'удаления';
                $errorCode = 'ORM_DELETE_ERROR';
            }

            throw new Error(
                sprintf('Ошибка %s %s: %s', $operationType, $entityTitle, implode(', ', $result->getErrorMessages())),
                $errorCode
            );
        }
    }

    public function getParamsMapAssoc()
    {
        $result = [];

        foreach($this->getParamsMap() as $param)
        {
            /**
             * @var \Mmit\NewSmile\CommandVariable\Base $param
             */

            $result[$param->getCode()] = $param;
        }

        return $result;
    }

    /**
     * Возвращает объект параметра с указанным кодом (т.е. именно сам параметр, а не его значение)
     *
     * @param string $code
     *
     * @throws Error
     * @return \Mmit\NewSmile\CommandVariable\Base
     */
    protected static function getParam($code)
    {
        $command = new static([], null, true);

        $paramsMap = $command->getParamsMapAssoc();

        if(!isset($paramsMap[$code]))
        {
            throw new Error('У команды ' . $command->getCode() . ' нет параметра ' . $code, 'PARAM_NOT_EXISTS');
        }

        return $paramsMap[$code];
    }


    /**
     * Выполняет команду
     */
    abstract protected function doExecute();


    /**
     * Возвращает описание параметров команды. Массив описания каждого параметра может включать следующие ключи:<br>
     * 1. REQUIRED - параметр является обязательным
     * 2. TITLE - название параметра
     * 3. DEFAULT - значение по умолчанию
     * 4. OPERATION - код операции, доступ к которой необходим для возможности указать значение данного параметра
     *
     * @return array
     */
    abstract public function getParamsMap();


    /**
     * Возвращает описание команды
     * @return string
     */
    public function getDescription()
    {
        return ''; // TODO it must be abstract
    }

    /**
     * Возвращает описание формата результата в виде массива
     * @return array
     */
    public function getResultFormat()
    {
        return [];
    }
}