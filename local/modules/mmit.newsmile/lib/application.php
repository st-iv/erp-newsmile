<?


namespace Mmit\NewSmile;

use Bitrix\Main\EventManager;
use Mmit\NewSmile\Access;
use Mmit\NewSmile\Command;

class Application
{
    /**
     * @var Application
     */
    protected static $instance;
    protected $reactRenderPoints = [];

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Access\Controller
     */
    protected $accessController;

    protected function __construct()
    {
        EventManager::getInstance()->addEventHandler('main', 'OnAfterUserAuthorize', function ()
        {
            static::getInstance()->clearUser();
        });
    }

    /**
     * @return Application
     */
    public static function getInstance()
    {
        if (!isset(static::$instance))
        {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function init()
    {

    }


    public function renderReactComponent($componentName, array $data = [], $rootClass = '')
    {

        $rootId = 'react-render-' . Helpers::getSnakeCase($componentName);


        array_walk_recursive($data, function(&$value, $key)
        {
            if($value instanceof Command\Base)
            {
                $value->execute();
                $value = $value->getResult();
            }
        });

        $this->addReactRoot($componentName, $rootId, $data);
        ?>
        <div id="<?=$rootId?>" class="<?=($rootClass ?: '')?>"></div>
        <?
    }

    public function addReactRoot($componentName, $rootId, $props = [])
    {
        $this->reactRenderPoints[$componentName] = [
            'ROOT_ID' => $rootId,
            'PROPS' => $props
        ];
    }

    public function includeReact()
    {
        if($this->reactRenderPoints)
        {
            ?>
            <script>
                <?foreach ($this->reactRenderPoints as $componentName => $componentData):?>
                ReactDOM.render(
                    React.createElement(
                        <?=$componentName?>,
                        <?=($componentData['PROPS'] ? json_encode($componentData['PROPS'], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK) : 'null')?>
                    ),
                    document.getElementById('<?=$componentData['ROOT_ID']?>')
                );
                <?endforeach;?>
            </script>
            <?
        }

    }

    /**
     * Возвращает объект текущего пользователя
     * @return User
     */
    public function getUser()
    {
        if (!isset($this->user))
        {
            $this->user = new User();
        }

        return $this->user;
    }

    public function getAccessController()
    {
        if (!isset($this->accessController))
        {
            $this->accessController = new Access\Controller();
        }

        return $this->accessController;
    }

    public function clearUser()
    {
        $this->user = null;
    }
}