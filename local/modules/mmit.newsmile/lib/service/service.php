<?

namespace Mmit\NewSmile\Service;

use Bitrix\Main\Entity;
use Mmit\NewSmile\Orm\ExtendedFieldsDescriptor;

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
                'values' => array('UNIT', 'TOOTH', 'JAW', 'BOTH_JAWS', 'ORAL_CAVITY', 'CONSULT', 'PRODUCT'),
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

    public static function getEnumVariantsTitles($enumFieldName)
    {
        return static::$enumVariantsTitles[$enumFieldName];
    }
}