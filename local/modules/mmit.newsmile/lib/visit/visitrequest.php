<?

namespace Mmit\NewSmile\Visit;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Orm\ExtendedFieldsDescriptor;
use Mmit\NewSmile\PatientCardTable;
use Mmit\NewSmile\Service\ServiceTable;

class VisitRequestTable extends DataManager implements ExtendedFieldsDescriptor
{
    protected static $enumVariants = [
        'STATUS' => [
            'WAITING' => 'ожидает рассмотрения',
            'CANCELED' => 'отменена',
            'PROCESSED' => 'обработана'
        ]
    ];

    public static function getTableName()
    {
        return 'm_newsmile_visit_request';
    }

    public static function getMap()
    {
        $statuses = array_keys(static::getEnumVariants('STATUS'));

        return [
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID',
            )),
            new Entity\DatetimeField('DATE_CREATE', array(
                'title' => 'Дата создания',
                'default_value' => new DateTime()
            )),
            new Entity\ReferenceField('SERVICE',
                ServiceTable::class,
                array('=this.SERVICE_ID' => 'ref.ID'),
                array(
                    'title' => 'Услуга'
                )
            ),
            new Entity\IntegerField('SERVICE_ID', [
                'title' => 'ID услуги'
            ]),
            new Entity\DatetimeField('DATE', [
                'title' => 'желаемая дата приёма'
            ]),
            new Entity\BooleanField('NEAR_FUTURE', [
                'title' => 'запись на ближайшее время'
            ]),
            new Entity\TextField('COMMENT', [
                'title' => 'комментарий'
            ]),
            new Entity\EnumField('STATUS', [
                'title' => 'статус',
                'default_value' => $statuses[0],
                'values' => $statuses
            ]),
            new Entity\ReferenceField('PATIENT',
                PatientCardTable::class,
                array('=this.PATIENT_ID' => 'ref.ID'),
                array(
                    'title' => 'Пациент'
                )
            ),
            new Entity\IntegerField('PATIENT_ID', [
                'title' => 'ID пациента'
            ]),
        ];
    }

    public static function getEnumVariants($enumFieldName)
    {
        return static::$enumVariants[$enumFieldName];
    }


}