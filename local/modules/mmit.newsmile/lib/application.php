<?


namespace Mmit\NewSmile;

use Bitrix\Main\EventManager;

class Application
{
    /**
     * @var Application
     */
    protected static $instance;

    /**
     * @var User
     */
    protected $user;

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

    public function clearUser()
    {
        $this->user = null;
    }
}