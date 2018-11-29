<?

namespace Mmit\NewSmile\Access;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\UserTable;

class RoleTable extends DataManager
{
    const ADMIN = 'admin';
    const DOCTOR = 'doctor';

    protected static $rolesUsersCache = [];

    public static function getTableName()
    {
        return 'm_newsmile_access_role';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID'
            ]),
            new Entity\StringField('CODE', [
                'required' => true,
                'title' => 'Код'
            ]),
            new Entity\StringField('NAME', [
                'required' => true,
                'title' => 'Название'
            ]),
            new OneToMany('OPERATIONS', RoleOperationTable::class, 'ROLE')
        ];
    }

    public static function getUsersByRole($role, array $queryParams = [])
    {
        $cacheId = $role . '_' . serialize($queryParams);

        if(isset(static::$rolesUsersCache[$cacheId]))
        {
            $result = static::$rolesUsersCache[$cacheId];
        }
        else
        {
            $result = [];

            $queryParams['filter']['UF_ROLES'] = $role;
            $dbUsers = UserTable::getList($queryParams);

            while($user = $dbUsers->fetch())
            {
                if(isset($user['ID']))
                {
                    $result[$user['ID']] = $user;
                }
                else
                {
                    $result[] = $user['ID'];
                }
            }

            static::$rolesUsersCache[$cacheId] = $result;
        }

        return $result;
    }
}