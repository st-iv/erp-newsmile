<?
namespace Mmit\NewSmile;

use Bitrix\Main\Entity;

class StoreTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_store';
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
                        new Entity\Validator\Unique()
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