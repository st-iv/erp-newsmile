<?

namespace Mmit\NewSmile\Access;

use Bitrix\Main\ORM\Objectify\Collection;
use Mmit\NewSmile\Application;
use Mmit\NewSmile\Access\RoleTable;
use Mmit\NewSmile\User;

class Controller
{
    protected $user;
    protected $operations;

    public function __construct(User $user = null)
    {
        if(!$user)
        {
            $user = Application::getInstance()->getUser();
        }

        $this->user = $user;
        $this->loadOperations();
    }

    public function isOperationAllowed($entityType, $operationCode, $entityIndex = null)
    {
        return isset($this->operations[$entityType][$operationCode]);
    }

    protected function loadOperations()
    {
        $dbRoles = RoleTable::getList([
            'select' => ['ID', 'CODE', 'OPERATIONS'],
            'filter' => [
                'CODE' => $this->user->getRoles()
            ]
        ]);

        $operationsRolesMap = [];

        while($role = $dbRoles->fetchObject())
        {
            /**
             * @var Collection $operations
             */
            $operations = $role->get('OPERATIONS');

            foreach ($operations as $operation)
            {
                $operationsRolesMap[$operation->get('OPERATION_ID')][] = $role->get('CODE');
            }
        }

        $dbOperations = Operation\OperationTable::getList([
            'cache' => [
                'ttl' => 3600000
            ]
        ]);

        while($operation = $dbOperations->fetch())
        {
            if(isset($operationsRolesMap[$operation['ID']]))
            {
                $this->operations[$operation['ENTITY_CODE']][$operation['CODE']] = [
                    'NAME' => $operation['NAME'],
                    'ROLES' => $operationsRolesMap[$operation['ID']]
                ];
            }
        }
    }

}