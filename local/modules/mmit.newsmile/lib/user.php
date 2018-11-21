<?

namespace Mmit\NewSmile;

use Bitrix\Main\UserTable;
use Mmit\NewSmile\Status\PatientTable;

class User
{
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

    public function loadRoles()
    {
        if(!isset($this->roles))
        {
            $dbUser = UserTable::getByPrimary($GLOBALS['USER']->GetID(), [
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

    public function loadData()
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


    public function getId()
    {
        $id = $this->id;

        if($this->is('patient') || $this->is('doctor'))
        {
            $id = $this->getField('ID');
        }

        return $id;
    }

    public function is($roleName)
    {
        return isset($this->roles[$roleName]);
    }
}