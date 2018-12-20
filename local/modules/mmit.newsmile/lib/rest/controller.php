<?

namespace Mmit\NewSmile\Rest;

use Bitrix\Main\Application;
use Bitrix\Main\UrlRewriter;
use Mmit\NewSmile\Config;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile;
use Mmit\NewSmile\Command;

class Controller
{
    protected $request;

    public function __construct()
    {
        $this->request = Application::getInstance()->getContext()->getRequest();
    }

    public function process()
    {
        $entity = $this->request['entity'];
        $command = $this->request['action'];

        if(isset($this->request['help']))
        {
            $this->renderHelpPage($entity, $command);
        }
        else
        {
            $this->executeCommand($entity, $command);
        }
    }

    protected function renderHelpPage($entity, $command)
    {
        // подумать как можно это реализовать
    }

    protected function executeCommand($entity, $command)
    {
        $error = null;
        $responseData = null;

        if(strlen($entity))
        {
            if(strlen($command))
            {
                $error = $this->checkAuth();

                if(!$error)
                {
                    /* поиск и запуск команды */
                    $commandNamespace = Helpers::getNamespace(Command\Base::class) . '\\' . Helpers::getCamelCase($entity);
                    $commandClass = $commandNamespace . '\\' . Helpers::getCamelCase($this->request['action']);

                    if(class_exists($commandClass) && is_subclass_of($commandClass, Command\Base::class))
                    {
                        /**
                         * @var Command\Base $command
                         */

                        try
                        {
                            $command = new $commandClass($this->request);
                            $command->execute();
                            $responseData = $command->getResult();
                            $error = $command->getError();
                        }
                        catch(Error $e)
                        {
                            $error = $e;
                        }
                    }
                    else
                    {
                        $error = new Error('Команда ' . $entity . '.' . $this->request['action'] . ' не поддерживается');
                    }
                }
            }
            else
            {
                $error = new Error('Не указана команда', 'COMMAND_IS_NOT_SPECIFIED');
            }
        }
        else
        {
            $error = new Error('Не указана сущность', 'ENTITY_IS_NOT_SPECIFIED');
        }

        /* формирование ответа */

        $response = [
            'result' => $error ? 'fail' : 'success',
            'data' => $responseData,
        ];

        if($error)
        {
            $response['error'] = [
                'code' => $error->getCharCode(),
                'description' => $error->getMessage()
            ];
        }
        else
        {
            $response['error'] = null;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Проверка авторизационного токена и авторизация
     */
    public function checkAuth()
    {
        $error = null;

        if(($this->request['entity'] != 'auth') && !NewSmile\Application::getInstance()->getUser()->isAuthorized())
        {
            if($this->request['token'])
            {
                $authorizeCommand = new Command\Auth\Authorize([
                    'token' => $this->request['token'],
                    'get_user_info' => false
                ]);

                $authorizeCommand->execute();
                $error = $authorizeCommand->getError();
            }
            else
            {
                $error = new Error('Не указан авторизационный токен', 'TOKEN_IS_NOT_SPECIFIED');
            }
        }

        return $error;
    }

    public static function installSefRule($restSection)
    {
        $entityRule = [
            'CONDITION' => '#^' . $restSection . '/([^/]+)/.*#',
            'PATH' => $restSection . '/index.php',
            'RULE' => 'entity=$1'
        ];

        UrlRewriter::add(Config::getSiteId(), $entityRule);

        $actionsRule = [
            'CONDITION' => '#^' . $restSection . '/([^/]+)/([^/]+)/.*#',
            'PATH' => $restSection . '/index.php',
            'RULE' => 'entity=$1&action=$2'
        ];

        UrlRewriter::add(Config::getSiteId(), $actionsRule);
    }
}