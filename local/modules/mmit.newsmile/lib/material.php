<?
namespace Mmit\NewSmile;

use Bitrix\Main\Entity;

class MaterialTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_material';
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
                    'title' => 'Группа'
                )
            ),
            new Entity\IntegerField('GROUP_ID', array(
                'title' => 'Группа'
            )),
            new Entity\ReferenceField('MEASURE',
                self::class,
                array('=this.MEASURE_ID' => 'ref.ID'),
                array(
                    'title' => 'Единица измерения',
                    'required' => true,
                )
            ),
            new Entity\IntegerField('MEASURE_ID', array(
                'title' => 'Единица измерения',
                'required' => true
            )),
        );
    }
}