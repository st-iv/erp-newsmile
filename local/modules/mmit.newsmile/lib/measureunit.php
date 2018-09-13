<?

namespace Mmit\NewSmile;

use Bitrix\Main\Entity;

class MeasureUnitTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_measureunit';
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
                'title' => 'Полное название',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 255),
                    );
                },
            )),
            new Entity\StringField('SHORT_NAME', array(
                'required' => true,
                'title' => 'Краткое название',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Length(null, 10),
                    );
                },
            )),
            new Entity\BooleanField('ACTIVE',
                array(
                    'title' => 'Активность',
                    'default_value' => 1
                )
            ),
        );
    }
}