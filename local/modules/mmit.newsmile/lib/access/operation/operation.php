<?


namespace Mmit\NewSmile\Access\Operation;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity;

class OperationTable extends DataManager
{
    protected static $operations;

    public static function getTableName()
    {
        return 'm_newsmile_access_operation';
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
            new Entity\StringField('ENTITY_CODE', [
                'required' => true,
                'title' => 'Код сущности'
            ]),
        ];
    }

    protected static function load()
    {
        $dbOperations = static::getList([
            'cache' => [
                'ttl' => 3600000
            ]
        ]);

        while($operation = $dbOperations->fetch())
        {
            static::$operations[$operation['ENTITY_CODE']][$operation['CODE']] = $operation['NAME'];
        }
    }

    public static function getByEntityCode($entityCode)
    {
        if(!static::$operations)
        {
            static::load();
        }

        return static::$operations[$entityCode];
    }
}