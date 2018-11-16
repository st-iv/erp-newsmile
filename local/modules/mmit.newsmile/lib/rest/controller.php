<?

namespace Mmit\NewSmile\Rest;

use Bitrix\Main\Application;
use Bitrix\Main\UrlRewriter;
use Mmit\NewSmile\Config;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Sms;

class Controller
{
    protected $request;
    const OPERATION_CONTROLLER_INTERFACE = __NAMESPACE__ . '\\Operation\\Controller';

    public function __construct()
    {
        $this->request = Application::getInstance()->getContext()->getRequest();
    }

    public function process()
    {
        $error = null;
        $responseData = [];
        $operation = $this->request['operation'];

        if(strlen($operation) > 0)
        {
            $operationControllerClass = __NAMESPACE__ . '\\Operation\\' . Helpers::getCamelCase($operation);

            if(class_exists($operationControllerClass) && is_subclass_of($operationControllerClass, static::OPERATION_CONTROLLER_INTERFACE))
            {
                /**
                 * @var \Mmit\NewSmile\Rest\Operation\Controller $operationController
                 */
                $operationController = new $operationControllerClass($operation);
                $operationController->process();

                $error = $operationController->getError();

                $responseData = $operationController->getResponseData();
            }
            else
            {
                $error = new Error('Операция ' . $this->request['operation'] . ' не поддерживается', 'OPERATION_IS_NOT_SUPPORTED');
            }
        }
        else
        {
            $error = new Error('Не указана операция', 'OPERATION_IS_NOT_SPECIFIED');
        }

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

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public static function installSefRule($restSection)
    {
        $rule = [
            'CONDITION' => '#^' . $restSection . '/([^/]+)/.*#',
            'PATH' => $restSection . '/index.php',
            'RULE' => 'operation=$1'
        ];

        UrlRewriter::add(Config::getSiteId(), $rule);
    }
}