<?

namespace Mmit\NewSmile\Rest\Documentation;

use Mmit\NewSmile\Command;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Rest;

class Documentator
{
    const TEMPLATES_FOLDER = __DIR__ . '/templates';

    protected $baseDir;

    public function __construct($baseDir)
    {
        $this->baseDir = $baseDir;
    }

    public function renderHelpPage($entity, $command)
    {
        include static::TEMPLATES_FOLDER . '/header.php';

        if(isset($entity) && isset($command))
        {

        }
        elseif (isset($entity))
        {
            $entitiesList = $this->getEntitiesList($entity);

            $this->includeTemplate('entity', $entitiesList[0]);
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

        foreach ($this->getAllCommandsClasses() as $class)
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

    protected function getAllCommandsClasses()
    {
        $startIndex = count(get_declared_classes());

        Helpers::scanDir(Command\Base::getBaseCommandsPath(), function($filePath)
        {
            include_once $filePath;
        });

        $result = array_slice(get_declared_classes(), $startIndex);
        return array_filter($result, function($className)
        {
            return $className != 'Mmit\\NewSmile\\Helpers';
        });
    }

    protected function getEntityUrl($entityCode)
    {
        return Rest\Controller::getEntityPath($this->baseDir, $entityCode) . '?help';
    }

    protected function includeTemplate($templateName, array $data = [])
    {
        include static::TEMPLATES_FOLDER . '/' . $templateName . '.php';
    }
}