<?


namespace Mmit\NewSmile\Document;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;

class DocumentTable extends DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_document';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new Entity\DatetimeField('TIME_CREATE',[
                'default_value' => new DateTime()
            ]),
            new Entity\StringField('TYPE', [
                'required' => true
            ]),
            new Entity\TextField('DATA', [
                'serialized' => true
            ])
        ];
    }
}