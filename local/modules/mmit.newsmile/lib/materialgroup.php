<?

namespace Mmit\NewSmile;

use Bitrix\Main\Entity;

class MaterialGroupTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_materialgroup';
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
            new Entity\IntegerField('DEPTH_LEVEL', array(
                'title' => 'Уровень вложенности',
                'required' => true,
                'validation' => function () {
                    return array(
                        new Entity\Validator\Range(1),
                    );
                },
            )),
            new Entity\ReferenceField('GROUP',
                self::class,
                array('=this.GROUP_ID' => 'ref.ID'),
                array(
                    'title' => 'Родительская группа'
                )
            ),
            new Entity\IntegerField('GROUP_ID', array(
                'title' => 'Родительская Группа'
            )),
        );
    }

    public static function onBeforeAdd(Entity\Event $event)
    {
        $result = new Entity\EventResult;
        $fields = $event->getParameter('fields');

        static::saveDepthLevel($fields, $result, 'add');

        return $result;
    }

    public static function onBeforeUpdate(Entity\Event $event)
    {
        $result = new Entity\EventResult;
        $fields = $event->getParameter('fields');

        static::saveDepthLevel($fields, $result, 'update');

        return $result;
    }

    public static function onAfterDelete(Entity\Event $event)
    {
        $id = $event->getParameter('primary');
        static::cascadeDelete($id);
    }

    public static function cascadeDelete($parentId)
    {
        $dbChilds = static::getList(array(
            'filter' => array(
                'GROUP_ID' => $parentId
            ),
            'select' => array('ID')
        ));

        while($childGroup = $dbChilds->fetch())
        {
            static::delete($childGroup['ID']);
        }

        $dbChildElements = MaterialTable::getList(array(
            'filter' => array(
                'GROUP_ID' => $parentId
            ),
            'select' => array('ID')
        ));

        while($element = $dbChildElements->fetch())
        {
            MaterialTable::delete($element['ID']);
        }
    }

    protected static function saveDepthLevel($fields, Entity\EventResult $result, $action)
    {
        $depthLevel = 0;

        if(($action == 'update') && !isset($fields['GROUP_ID']))
        {
            // DEPTH_LEVEL нельзя устанавливать напрямую
            $result->unsetField('DEPTH_LEVEL');
        }
        elseif(!$fields['GROUP_ID'])
        {
            $depthLevel = 1;
        }
        else
        {
            $dbNewParentGroup = self::getList(array(
                'filter' => array(
                    'ID' => $fields['GROUP_ID']
                ),
                'select' => array('DEPTH_LEVEL')
            ));

            if($newParentGroup = $dbNewParentGroup->fetch())
            {
                $depthLevel = $newParentGroup['DEPTH_LEVEL'] + 1;
            }
        }

        if($depthLevel)
        {
            $result->modifyFields(array(
                'DEPTH_LEVEL' => $depthLevel
            ));
        }

        return $result;
    }
}