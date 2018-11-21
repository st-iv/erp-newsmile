<?

namespace Mmit\NewSmile\Service;

use Bitrix\Main\Entity;
use Mmit\NewSmile\Orm\ExtendedFieldsDescriptor;
use Mmit\NewSmile;

class ServiceTable extends Entity\DataManager implements ExtendedFieldsDescriptor
{
    protected static $enumVariantsTitles = array(
        'MEASURE' => array(
            'UNIT' => 'штука',
            'TOOTH' => 'зуб',
            'JAW' => 'челюсть',
            'BOTH_JAWS' => 'обе челюсти',
            'ORAL_CAVITY' => 'полость рта',
            'CONSULT' => 'консультация (осмотр)',
            'PRODUCT' => 'товар',
        )
    );

    public static function getTableName()
    {
        return 'm_newsmile_service';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID',
            )),
            new Entity\StringField('NAME', array(
                'required' => true,
                'title' => 'Название',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new Entity\EnumField('MEASURE', array(
                'title' => 'Единица измерения',
                'values' => array_keys(static::getEnumVariants('MEASURE')),
                'required' => true,
            )),
            new Entity\ReferenceField('GROUP',
                'Mmit\NewSmile\Service\GroupTable',
                array('=this.GROUP_ID' => 'ref.ID'),
                array(
                    'title' => 'Группа'
                )
            ),
            new Entity\IntegerField('GROUP_ID', array(
                'title' => 'Группа'
            )),
        );
    }

    public static function getEnumVariants($enumFieldName)
    {
        return static::$enumVariantsTitles[$enumFieldName];
    }

    public static function onAfterDelete(Entity\Event $event)
    {
        $id = $event->getParameter('primary');

        NewSmile\Orm\Helper::cascadeDelete($id, array(
            'Mmit\NewSmile\Service\PriceTable' => 'SERVICE_ID'
        ));
    }

    public static function get1LvlTree()
    {
        $dbServiceGroups = GroupTable::getList();
        $groups = [];
        $result = [];

        while($group = $dbServiceGroups->fetch())
        {
            $groups[$group['ID']] = $group;
        }


        $tree = NewSmile\Helpers::getTree($groups);
        $dbServices = static::getList([
            'select' => ['ID', 'NAME', 'GROUP_ID']
        ]);

        $allServices = [];

        while ($service = $dbServices->fetch())
        {
            $allServices[$service['GROUP_ID']][] = $service;
        }

        foreach ($tree as $firstLvlGroup)
        {
            $firstLvlGroup['SERVICES'] = static::getServicesRecursive($firstLvlGroup, $allServices);
            $result[$firstLvlGroup['ID']] = $firstLvlGroup;
        }

        return $result;
    }

    protected static  function getServicesRecursive($group, $allServices)
    {
        $result = $allServices[$group['ID']];

        foreach ($group['SUBGROUPS'] as $subgroup)
        {
            $result = array_merge($result, static::getServicesRecursive($subgroup, $allServices));
        }

        return $result;
    }
}