<?

namespace Mmit\NewSmile\Rest;

use Bitrix\Main\Application;
use Bitrix\Main\UrlRewriter;
use Mmit\NewSmile\Config;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Rest\Entity\Auth;

class Controller
{
    protected $request;
    const ENTITY_CONTROLLER_INTERFACE = __NAMESPACE__ . '\\Entity\\Controller';

    public function __construct()
    {
        $this->request = Application::getInstance()->getContext()->getRequest();
    }

    public function process()
    {
        $error = null;
        $responseData = [];
        $entity = $this->request['entity'];

        if(strlen($entity) > 0)
        {
            /* проверка авторизационного токена */
            if($entity != 'auth')
            {
               if($this->request['token'])
               {
                   $authController = new Auth();
                   $authController->authorize($this->request['token']);
                   $error = $authController->getError();
               }
               else
               {
                   $error = new Error('Не указан авторизационный токен', 'TOKEN_IS_NOT_SPECIFIED');
               }
            }

            if(!$error)
            {
                /* подключение контроллера указанной операции */

                $entityControllerClass = __NAMESPACE__ . '\\Entity\\' . Helpers::getCamelCase($entity);

                if(class_exists($entityControllerClass) && is_subclass_of($entityControllerClass, static::ENTITY_CONTROLLER_INTERFACE))
                {
                    /**
                     * @var \Mmit\NewSmile\Rest\Entity\Controller $entityController
                     */
                    $entityController = new $entityControllerClass();
                    $entityController->process($this->request['action']);
                    $error = $entityController->getError();
                    $responseData = $entityController->getResponseData();
                }
                else
                {
                    $error = new Error('Сущность ' . $this->request['entity'] . ' не поддерживается', 'ENTITY_IS_NOT_SUPPORTED');
                }
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