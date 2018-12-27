<?

namespace Mmit\NewSmile\CommandParam;

use Bitrix\Main\Diag\Debug;
use Mmit\NewSmile\Application;
use Mmit\NewSmile\Error;

abstract class Base
{
    protected $code;
    protected $title;
    protected $description;
    protected $required;
    protected $defaultValue;
    protected $operations;
    protected $rawValue;
    protected $defaultEntityCode;

    /**
     * @var \Mmit\NewSmile\Command\Base
     */
    protected $command;

    /**
     * Base constructor.
     *
     * @param string $code - символьнйы код параметра, рекомендуется задавать в camelCase
     * @param string $title - название параметра
     * @param string $description - описание параметра, выводится в описании rest API
     * @param bool $required - является ли параметр обязательным
     * @param null $defaultValue - значение параметра по умолчанию. Для обязательных параметров указывать значение по умолчанию нет смысла
     */
    public function __construct($code, $title, $description = '', $required = false, $defaultValue = null)
    {
        $this->code = $code;
        $this->title = $title;
        $this->description = $description;
        $this->required = $required;
        $this->defaultValue = $defaultValue;

        $this->init();
    }

    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string|array $operations
     *
     * @return $this
     */
    public function setOperations($operations)
    {
        if(!is_array($operations))
        {
            $operations = [$operations];
        }

        $this->operations = $operations;
        return $this;
    }

    /**
     * Связывает параметр с родительской командой.
     * @param \Mmit\NewSmile\Command\Base $command
     */
    public function setCommand(\Mmit\NewSmile\Command\Base $command)
    {
        $this->command = $command;
    }

    /**
     * Сохраняет необработаное значение параметра. При сохранении проверяется, доступно ли указание данного параметра для
     * текущего пользователя. Для обязательных параметров проверяется, является ли значение заполненным.
     *
     * @param $rawValue
     *
     * @throws Error
     */
    public function setRawValue($rawValue)
    {
        if($this->issetValue($rawValue))
        {
            if($this->isAllowed())
            {
                $this->rawValue = $rawValue;
            }
            else
            {
                throw new Error(
                    'Недостаточно прав для использования параметра ' . $this->code . ($this->command ? ' команды ' . $this->command->getCode() : ''),
                    'PARAM_ACCESS_DENIED'
                );
            }
        }
        elseif($this->required)
        {
            throw new Error(
                'Не указано значение обязательного параметра ' . $this->code . ($this->command ? ' команды ' . $this->command->getCode() : ''),
                'REQUIRED_PARAM_NOT_DEFINED'
            );
        }
    }

    protected function issetValue($value)
    {
        return (isset($value) && ($value !== ''));
    }

    /**
     * Сохраняет код сущности по умолчанию для проверки прав на использование параметра.
     * @param $entityCode
     */
    public function setDefaultEntityCode($entityCode)
    {
        $this->defaultEntityCode = $entityCode;
    }

    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Возвращает отформатированное значение параметра
     * @return mixed
     */
    final public function getFormattedValue()
    {
        if($this->getCode() == 'filter')
        {
            Debug::writeToFile($this->defaultValue);
            Debug::writeToFile($this->getCode());
        }


        if(isset($this->rawValue) && ($this->rawValue !== ''))
        {
            $result = $this->formatValue($this->rawValue);
        }
        elseif(isset($this->defaultValue))
        {
            $result = $this->formatValue($this->defaultValue);
        }
        else
        {
            $result = null;
        }

        return $result;
    }

    /**
     * Проверяет, разрешено ли данному пользователю указывать данный параметр
     * @return bool
     * @throws Error
     */
    public function isAllowed()
    {
        $result = true;
        $accessController = Application::getInstance()->getAccessController();

        foreach ($this->operations as $operationCode)
        {
            /*
            Коде операции может быть полным и неполным - с кодом сущности или без него. С учетом этого определяем отдельно
            код сущности и код операции. Если код неполный - используем сущность по умолчанию.
            */
            $operationCodeParts = explode('/', $operationCode);

            if(count($operationCodeParts) == 2)
            {
                $entityCode = $operationCodeParts[0];
                $operationCode = $operationCodeParts[1];
            }
            else
            {
                if(isset($this->defaultEntityCode))
                {
                    $entityCode = $this->defaultEntityCode;
                }
                else
                {
                    throw new Error('Не указана сущность по умолчанию для параметра ' . $this->code, 'DEFAULT_ENTITY_IS_NOT_DEFINED');
                }
            }

            if(!$accessController->isOperationAllowed($entityCode, $operationCode))
            {
                $result = false;
                break;
            }
        }

        return $result;
    }

    /**
     * Возмущается типом переданного значения
     * @param array $requiredTypes - поддерживаемые типы значения
     * @throws Error
     */
    protected function sayBadValueType(array $requiredTypes)
    {
        $message = 'Указанный тип значения параметра ' . $this->code;

        if($this->command)
        {
            $message .= ' команды ' . $this->command->getCode();
        }

        $message .= ' не поддерживается. Поддерживаемые типы: ' . implode(', ', $requiredTypes);

        throw new Error($message, 'PARAM_VALUE_BAD_TYPE');
    }

    /**
     * Возмущается форматом переданного значения
     * @param string $kosherFormat - описание необходимого формата
     * @throws Error
     */
    protected function sayBadValueFormat($kosherFormat = '')
    {
        $message = 'Указанный формат значения параметра ' . $this->code;

        if($this->command)
        {
            $message .= ' команды ' . $this->command->getCode();
        }

        $message .= ' не поддерживается.';

        if($kosherFormat)
        {
            $message .= ' Поддерживаемый формат: ' . $kosherFormat . '.';
        }

        throw new Error($message, 'PARAM_VALUE_BAD_FORMAT');
    }

    /**
     * Инициализирует параметр
     */
    protected function init()
    {
        return;
    }

    /**
     * Форматирует значение параметра
     * @param $value
     *
     * @return mixed
     */
    abstract protected function formatValue($value);
}