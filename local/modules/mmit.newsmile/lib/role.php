<?

namespace Mmit\NewSmile;

use Bitrix\Main\UserTable;

class RoleTable
{
    protected static $rolesUsersCache = [];

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