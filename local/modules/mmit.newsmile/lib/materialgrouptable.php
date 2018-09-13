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
}