<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 30.05.2018
 * Time: 16:19
 */
namespace Mmit\NewSmile;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;

Loc::loadMessages(__FILE__);

class VisitTable extends Entity\DataManager
{
    const STATUS_VISIT = 1;
    const STATUS_START = 2;
    const STATUS_PAYMENT = 3;
    const STATUS_CARD = 4;
    const STATUS_END = 5;
    const STATUS_CANCEL = 6;

    public static function getTableName()
    {
        return 'm_newsmile_visit';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID',
            )),
            new Entity\DatetimeField('TIMESTAMP_X', array(
                'title' => 'Дата создания',
                'default_value' => Date::createFromTimestamp(time())
            )),
            new Entity\IntegerField('STATUS_ID', array(
                'title' => 'STATUS_ID',
                'default_value' => 0
            )),
            new Entity\ReferenceField('STATUS',
                'Mmit\NewSmile\Status\Visit',
                array('=this.STATUS_ID' => 'ref.ID'),
                array(
                    'title' => 'Статус приема'
                )
            ),
            new Entity\DateField('DATE_START', array(
                'title' => 'Дата'
            )),
            new Entity\DatetimeField('TIME_START', array(
                'title' => 'Время начала'
            )),
            new Entity\DatetimeField('TIME_END', array(
                'title' => 'Время окончания'
            )),
            new Entity\IntegerField('PATIENT_ID', array(
                'title' => 'PATIENT_ID',
            )),
            new Entity\ReferenceField('PATIENT',
                'Mmit\NewSmile\PatientCard',
                array('=this.PATIENT_ID' => 'ref.ID'),
                array(
                    'title' => 'Карточка пациента'
                )
            ),
            new Entity\IntegerField('DOCTOR_ID', array(
                'title' => 'DOCTOR_ID',
            )),
            new Entity\ReferenceField('DOCTOR',
                'Mmit\NewSmile\Doctor',
                array('=this.DOCTOR_ID' => 'ref.ID'),
                array(
                    'title' => 'Врач'
                )
            ),
            new Entity\IntegerField('WORK_CHAIR_ID', array(
                'title' => 'WORK_CHAIR_ID',
            )),
            new Entity\ReferenceField('WORK_CHAIR',
                'Mmit\NewSmile\WorkChair',
                array('=this.WORK_CHAIR_ID' => 'ref.ID'),
                array(
                    'title' => 'Кресло'
                )
            ),
            new Entity\IntegerField('CLINIC_ID', array(
                'title' => 'CLINIC_ID',
                'default_value' => 1
            )),
            new Entity\ReferenceField('CLINIC',
                'Mmit\NewSmile\Clinic',
                array('=this.CLINIC_ID' => 'ref.ID'),
                array(
                    'title' => 'Клиника'
                )
            ),
        );
    }

    public function getCountVisitFromDate($arFilter)
    {
        global $DB;
        $strSqlSelect = "SELECT `DATE_START`, COUNT(*) AS `COUNT` ";
        $strSqlFrom = "FROM `m_newsmile_visit` ";
        $strSqlWhere = "WHERE ";
        $isFirthWhere = true;
        foreach ($arFilter as $field => $value)
        {
            if ($isFirthWhere) {
                $isFirthWhere = false;
            } else {
                $strSqlWhere .= " AND ";
            }
            $strFirth = substr($field, 0, 1);
            $strSecond = substr($field, 1, 1);
            if (in_array($strFirth, array('!','=','>','<'))) {
                if (in_array($strSecond, array('='))) {
                    $strSqlWhere .= "`" . substr($field, 2) . "` " . $strFirth . $strSecond . " '" . $value . "' ";
                }
            }
        }
        $strSqlGroup = "GROUP BY `DATE_START`";
        $result = $DB->Query(
            $strSqlSelect .
            $strSqlFrom .
            $strSqlWhere .
            $strSqlGroup
        );
        return $result;
    }

    public static function createStatus()
    {
        $arFields = [
            [
                'NAME' => 'Пациент ожидает'
            ],
            [
                'NAME' => 'Выполняется'
            ],
            [
                'NAME' => 'Оплата'
            ],
            [
                'NAME' => 'Заполнение истории болезни'
            ],
            [
                'NAME' => 'Завершен'
            ],
            [
                'NAME' => 'Отменен'
            ],
        ];
        foreach ($arFields as $item) {
            Status\VisitTable::add($item);
        }
    }
}
