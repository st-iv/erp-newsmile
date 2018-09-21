<?

namespace Mmit\NewSmile\Service;

use Bitrix\Main\Entity;
use Mmit\NewSmile;

class GroupTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_service_group';
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

    public static function onAfterDelete(Entity\Event $event)
    {
        $id = $event->getParameter('primary');

        NewSmile\Orm\Helper::cascadeDelete($id, array(
            self::class => 'GROUP_ID',
            'Mmit\NewSmile\Service\ServiceTable' => 'GROUP_ID'
        ));
    }
}