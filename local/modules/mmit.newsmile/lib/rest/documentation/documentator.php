<?

namespace Mmit\NewSmile\Rest\Documentation;

use Mmit\NewSmile\Command;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Rest;

class Documentator
{
    const TEMPLATES_FOLDER = __DIR__ . '/templates';

    protected static $commandsClasses;

    protected $baseDir;

    public function __construct($baseDir)
    {
        $this->baseDir = $baseDir;
    }

    public function renderHelpPage($entityCode, $commandCode)
    {
        include static::TEMPLATES_FOLDER . '/header.php';

        if(isset($entityCode) && isset($commandCode))
        {
            $data = $this->getCommandsList($entityCode, $commandCode, true)[0];
            $data['ENTITY'] = $this->getEntitiesList($entityCode)[0];
            $data['MAIN_PAGE_URL'] = $this->baseDir . '/?help';

            $this->includeTemplate('command', $data);
        }
        elseif (isset($entityCode))
        {
            $entitiesList = $this->getEntitiesList($entityCode);
            $this->includeTemplate('entity', array_merge($entitiesList[0], [
                'COMMANDS' => $this->getCommandsList($entityCode),
                'MAIN_PAGE_URL' => $this->baseDir . '/?help'
            ]));
        }
        else
        {
            $this->includeTemplate('main', [
                'ENTITIES' => $this->getEntitiesList()
            ]);
        }

        include static::TEMPLATES_FOLDER . '/footer.php';
    }


    protected function getEntitiesList($filterByCode = null)
    {
        $result = [];

        foreach ($this->getCommandsClasses() as $class)
        {
            if(is_subclass_of($class, Command\EntityDescriptor::class))
            {
                /**
                 * @var Command\EntityDescriptor $descriptor
                 */
                $descriptor = new $class();
                $entityCode = $descriptor->getCode();

                if(!$filterByCode || ($filterByCode == $entityCode))
                {
                    $result[] = [
                        'CODE' => $entityCode,
                        'DESCRIPTION' => $descriptor->getDescription(),
                        'URL' => $this->getEntityUrl($entityCode)
                    ];

                    if($filterByCode)
                    {
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param string $entity - код сущности
     * @param string $filterByCode - код команды, если указан, то будет возвращена информация только по этой команде
     * @param bool $bDetailInfo - флаг запроса детальной информации по командам
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function getCommandsList($entity, $filterByCode = '', $bDetailInfo = false)
    {
        $result = [];

        foreach ($this->getCommandsClasses() as $class)
        {
            if (is_subclass_of($class, Command\Base::class))
            {
                $reflector = new \ReflectionClass($class);

                /**
                 * @var Command\Base $class
                 * @var Command\Base $command
                 */
                if($reflector->isAbstract() || $entity != $class::getEntityCode()) continue;


                $command = new $class([], null, true);
                $commandCode = $command::getShortCode();

                if(!$filterByCode || ($commandCode == $filterByCode))
                {
                    $commandInfo = [
                        'CODE' => $commandCode,
                        'DESCRIPTION' => $command->getDescription(),
                        'URL' => $this->getCommandUrl($entity, $commandCode),
                    ];

                    if($bDetailInfo)
                    {
                        $commandInfo['FULL_CODE'] = $command::getCode();
                        $commandInfo['RESULT_FORMAT'] = $command->getResultFormat();
                        $commandInfo['PARAMS'] = $command->getParamsMap();
                    }

                    $result[] = $commandInfo;
                }
            }
        }

        sort($result);

        return $result;
    }

    /**
     * Получает классы команд в системе
     * @param string $entity - код сущности. Если указан, то будут выбраны только те классы команд, которые относятся к
     * этой сущности
     *
     * @return array
     */
    protected function getCommandsClasses()
    {
        if(!isset(static::$commandsClasses))
        {
            $startIndex = count(get_declared_classes());

            Helpers::scanDir(Command\Base::getBaseCommandsPath(), function($filePath)
            {
                include_once $filePath;
            });

            $result = array_slice(get_declared_classes(), $startIndex);

            static::$commandsClasses = array_filter($result, function($className)
            {
                return $className != 'Mmit\\NewSmile\\Helpers';
            });
        }

        return static::$commandsClasses;
    }

    protected function getEntityUrl($entityCode)
    {
        return Rest\Controller::getEntityUrl($this->baseDir, $entityCode) . '?help';
    }

    protected function getCommandUrl($entityCode, $commandCode)
    {
        return Rest\Controller::getCommandUrl($this->baseDir, $entityCode, $commandCode) . '?help';
    }

    protected function includeTemplate($templateName, array $data = [])
    {
        include static::TEMPLATES_FOLDER . '/' . $templateName . '.php';
    }
}