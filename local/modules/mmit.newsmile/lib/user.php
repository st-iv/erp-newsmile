<?

namespace Mmit\NewSmile;

use Bitrix\Main\UserTable;
use Mmit\NewSmile\Status\PatientTable;

/**
 * Представляет пользователя Newsmile. Каждому пользователю newsmile соответствует пользователь битрикс,
 * и пользователи в из таблиц модуля Newsmile - в зависимости от роли (PatientCardTable, DoctorTable...)
 * Class User
 * @package Mmit\NewSmile
 */
class User
{
    /**
     * @var int id пользователя Bitrix
     */
    protected $id;
    protected $data;

    /**
     * @var \Bitrix\Main\ORM\Entity $entity
     */
    protected $entity;

    protected $roles;

    public function __construct($userId = 0)
    {
        if(!$userId)
        {
            $userId = $GLOBALS['USER']->GetID();
        }

        if($userId)
        {
            $this->id = $userId;
            $this->loadRoles();

            if($this->is('patient'))
            {
                $this->entity = PatientCardTable::getEntity();
            }
            elseif($this->is('doctor'))
            {
                $this->entity = DoctorTable::getEntity();
            }

            $this->loadData();
        }
    }

    protected function loadRoles()
    {
        if(!isset($this->roles))
        {
            $dbUser = UserTable::getByPrimary($this->id, [
                'select' => ['UF_ROLES']
            ]);

            if($user = $dbUser->fetch())
            {
                $this->roles = array_flip($user['UF_ROLES']);
            }
            else
            {
                $this->roles = [];
            }
        }
    }

    protected function loadData()
    {
        if(!isset($this->data) && (isset($this->entity)))
        {
            $dataClass = $this->entity->getDataClass();
            $dbData = $dataClass::getList([
                'filter' => [
                    'USER_ID' => $this->id,
                ]
            ]);

            $this->data = $dbData->fetch();
        }
    }

    public function getField($name)
    {
        return $this->data[$name];
    }

    /**
     * Получает id пользователя newsmile
     * @return int
     */
    public function getId()
    {
        $id = $this->id;

        if($this->is('patient') || $this->is('doctor'))
        {
            $id = $this->getField('ID');
        }

        return $id;
    }

    /**
     * Проверяет соответствие пользователя указанным ролям
     * @param string|array $roles - код роли, либо массив кодов
     * @param bool $bAndLogic - в случае, если первый параметр - массив, этот флаг определяет логику сравнения. При значении
     * true - будет проверено, обладает ли пользователь всеми ролями из указанных в массиве, иначе будет достаточно только
     * одной роли из массива
     *
     * @return bool
     */
    public function is($roles, $bAndLogic = false)
    {
        if(is_array($roles))
        {
            $diff = array_diff($roles, $this->roles);

            if($bAndLogic)
            {
                $result = (count($diff) == 0);
            }
            else
            {
                $result = (count($roles) > count($diff));
            }
        }
        else
        {
            $result = isset($this->roles[$roles]);
        }

        return $result;
    }

    public function getRoles()
    {
        return array_keys($this->roles);
    }

    public function isAuthorized()
    {
        global $USER;

        return ($this->id == $USER->GetID()) && $USER->IsAuthorized();
    }

    public function getBitrixUserFields(array $select)
    {
        if($this->isAuthorized())
        {
            $result = UserTable::getByPrimary($this->id, [
                'select' => $select
            ])->fetchAll();
        }
        else
        {
            throw new Error('Невозможно чтение полей пользователя - пользователь не авторизован', 'USER_NOT_AUTHORIZED');
        }

        return $result[0];
    }

    public function setBitrixUserFields(array $fields)
    {
        global $USER;
        $isSuccess = $USER->Update($USER->GetID(), $fields);
        if(!$isSuccess)
        {
            throw new Error('Ошибка обновления полей пользователя: ' . $USER->LAST_ERROR, 'USER_UPDATE_ERROR');
        }
    }
}