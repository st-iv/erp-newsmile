<?

namespace Mmit\NewSmile;

use Bitrix\Main\Entity;
use Mmit\NewSmile\Orm;

class DoctorSpecializationTable extends Entity\DataManager implements Orm\ExtendedFieldsDescriptor
{
    protected static $enumVariantsTitles = array(
        'SPECIALIZATION' => array(
            'DOCTOR' => 'Врач',
            'SURGEON' => 'Хирург',
            'HYGIENIST' => 'Гигиенист',
            'PARODONTOLOGIST' => 'Пародонтолог',
        )
    );

    public static function getTableName()
    {
        return 'm_newsmile_doctor_specialization';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID',
            )),
            new Entity\ReferenceField('DOCTOR',
                'Mmit\NewSmile\Doctor',
                array('=this.DOCTOR_ID' => 'ref.ID'),
                array(
                    'title' => 'Врач'
                )
            ),
            new Entity\IntegerField('DOCTOR_ID', array(
                'title' => 'Врач',
            )),
            new Entity\EnumField('SPECIALIZATION', array(
                'title' => 'Специализация',
                'values' => array_keys(static::getEnumVariants('SPECIALIZATION')),
            )),
        );
    }

    public static function getEnumVariants($enumFieldName)
    {
        return static::$enumVariantsTitles[$enumFieldName];
    }

    public static function getSpecName($specializationCode)
    {
        return static::$enumVariantsTitles['SPECIALIZATION'][$specializationCode];
    }
}