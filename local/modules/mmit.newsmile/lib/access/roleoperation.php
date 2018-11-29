<?


namespace Mmit\NewSmile\Access;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class RoleOperationTable extends DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_access_role_operation';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ROLE_ID', [
                'primary' => true,
                'title' => 'ROLE_ID'
            ]),
            new Reference('ROLE', RoleTable::class, Join::on('this.ROLE_ID', 'ref.ID')),
            new Entity\IntegerField('OPERATION_ID', [
                'primary' => true,
                'title' => 'OPERATION_ID'
            ]),
            new Reference('OPERATION', Operation\OperationTable::class, Join::on('this.OPERATION_ID', 'ref.ID')),
        ];
    }
}