<?

namespace Mmit\NewSmile\Service;

use Bitrix\Main\Entity,
    Bitrix\Main\Type\DateTime;

class PriceHistoryTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_service_price_history';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID',
            )),
            new Entity\DatetimeField('DATE', array(
                'title' => 'Дата',
                'default_value' => DateTime::createFromTimestamp(time())
            )),
            new Entity\ReferenceField('SERVICE',
                'Mmit\NewSmile\Service\ServiceTable',
                array('=this.SERVICE_ID' => 'ref.ID'),
                array(
                    'title' => 'Услуга'
                )
            ),
            new Entity\IntegerField('SERVICE_ID', array(
                'title' => 'Услуга',
                'primary' => true,
            )),
            new Entity\ReferenceField('CLINIC',
                'Mmit\NewSmile\ClinicTable',
                array('=this.CLINIC_ID' => 'ref.ID'),
                array(
                    'title' => 'Клиника'
                )
            ),
            new Entity\IntegerField('CLINIC_ID', array(
                'title' => 'Клиника',
                'primary' => true,
            )),
            new Entity\FloatField('PRICE', array(
                'title' => 'Цена',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Range(0),
                    );
                },
            )),
        );
    }
}