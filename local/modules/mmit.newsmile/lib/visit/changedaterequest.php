<?

namespace Mmit\NewSmile\Visit;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity;

class ChangeDateRequestTable extends DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_visit_change_date_request';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('VISIT_ID', [
                'primary' => true
            ]),
            new Entity\ReferenceField('VISIT',
                VisitTable::class,
                array('=this.VISIT_ID' => 'ref.ID'),
                array(
                    'title' => 'Услуга'
                )
            ),
            new Entity\DatetimeField('NEW_DATE', [
                'required' => true
            ])
        ];
    }
}