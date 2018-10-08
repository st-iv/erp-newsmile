<?
namespace Mmit\NewSmile\Notice;

use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\VisitTable;
use Mmit\NewSmile\Date;

class NoticeTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'm_newsmile_notice';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => 'ID',
            )),
            new Entity\DatetimeField('TIME', array(
                'title' => 'Время',
                'default_value' => DateTime::createFromTimestamp(time())
            )),
            new Entity\ReferenceField('TYPE',
                'Mmit\NewSmile\Notice\Type',
                array('=this.TYPE_ID' => 'ref.ID'),
                array(
                    'title' => 'Тип'
                )
            ),
            new Entity\IntegerField('TYPE_ID', array(
                'title' => 'Тип',
                'required' => true
            )),
            new Entity\BooleanField('IS_READ', array(
                'title' => 'Прочитано',
                'default_value' => 0
            )),
            new Entity\TextField('PARAMS', array(
                'title' => 'Параметры',
                'serialized' => true
            )),
            new Entity\ReferenceField('USER',
                'Bitrix\Main\User',
                array('=this.USER_ID' => 'ref.ID'),
                array(
                    'title' => 'Получатель'
                )
            ),
            new Entity\IntegerField('USER_ID', array(
                'title' => 'Получатель',
                'required' => true
            ))
        );
    }

    public static function add(array $data)
    {
        if(isset($data['TYPE']))
        {
            $data['TYPE_ID'] = TypeTable::getIdByCode($data['TYPE']);
            unset($data['TYPE']);
        }

        return parent::add($data);
    }

    /**
     * Добавляет в массив полей уведомления поля типа уведомления, в полях TITLE, TEXT и в параметре
     * LINK подставляет значения параметров уведомления.
     * @param array $noticeData поля уведомления
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function  extendNoticeDataByType(array &$noticeData)
    {
        $typeInfo = TypeTable::getTypeInfo($noticeData['TYPE_ID']);

        $noticeData = array_merge($noticeData, $typeInfo);
        $noticeData['TYPE'] = $noticeData['CODE'];
        unset($noticeData['CODE']);

        if($noticeData['PARAMS'])
        {
            $searchParams = array_keys($noticeData['PARAMS']);
            array_walk($searchParams, function(&$paramName)
            {
                $paramName = '#' . $paramName . '#';
            });

            $replaceParams = array_values($noticeData['PARAMS']);
            $searchSubjects = array($typeInfo['TITLE'], $typeInfo['TEXT']);

            if($noticeData['PARAMS']['LINK'])
            {
                $searchSubjects[] = $noticeData['PARAMS']['LINK'];
            }

            $replaceResults = str_replace($searchParams, $replaceParams, $searchSubjects);

            $noticeData['TITLE'] = $replaceResults[0];
            $noticeData['TEXT'] = $replaceResults[1];

            if($noticeData['PARAMS']['LINK'])
            {
                $noticeData['PARAMS']['LINK'] = $replaceResults[2];
            }
        }
    }

    /**
     * Добавляет уведомление в бд и отправляет получателям
     * @param string $typeCode - символьный код типа
     * @param array $users - id пользователей-получателей
     * @param array $params - параметры уведомления
     * @param bool $bUseParamModificator - использовать модификатор параметров
     *
     * @throws \Exception
     */
    public static function push($typeCode, array $params = array(), array $users = array(), $bUseParamModificator = true)
    {
        $sendToUsers = array();
        $noticeId = null;

        if($bUseParamModificator && $params)
        {
            // применяем модификатор параметров, если он есть
            $modifierName = 'modifyParams' . Helpers::getCamelCase($typeCode);

            if(method_exists(static::class, $modifierName))
            {
                $params = static::{$modifierName}($params);
            }
        }

        $noticeData = array(
            'TYPE' => $typeCode,
            'PARAMS' => $params
        );

        if(!$users)
        {
            $users[] = $GLOBALS['USER']->GetID();
        }

        foreach ($users as $userId)
        {
            $currentNoticeData = $noticeData;
            $currentNoticeData['USER_ID'] = $userId;

            $addResult = static::add($currentNoticeData);

            if($addResult->isSuccess())
            {
                $sendToUsers[] = $userId;
                $noticeId = $addResult->getId();
            }
        }

        if($sendToUsers && $noticeId)
        {
            static::send($noticeId, $sendToUsers);
        }
    }

    /**
     * Отправляет уведомление получателям
     * @param int $noticeId - id уведомления
     * @param array $users - id пользователей-получателей
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected static function send($noticeId, array $users)
    {
        if(!Loader::includeModule('pull')) return;

        \CPullStack::AddByUsers($users, array(
            'module_id' => 'mmit.newsmile',
            'command' => 'add_notice',
            'params' => array(
                'ID' => $noticeId
            )
        ));
    }

    /* модификаторы параметров уведомлений */

    protected static function modifyParamsVisitFinished($params)
    {
        if($params['VISIT_ID'])
        {
            $dbVisit = VisitTable::getByPrimary($params['VISIT_ID'], array(
                'select' => array(
                    'PATIENT_NAME' => 'PATIENT.NAME',
                    'PATIENT_LAST_NAME' => 'PATIENT.LAST_NAME',
                    'PATIENT_SECOND_NAME' => 'PATIENT.SECOND_NAME',
                    'PATIENT_BIRTHDAY' => 'PATIENT.PERSONAL_BIRTHDAY',
                    'DOCTOR_NAME' => 'DOCTOR.NAME',
                    'DOCTOR_LAST_NAME' => 'DOCTOR.LAST_NAME',
                    'DOCTOR_SECOND_NAME' => 'DOCTOR.SECOND_NAME',
                    'DOCTOR_COLOR' => 'DOCTOR.COLOR',
                )
            ));
            
            if($visit = $dbVisit->fetch())
            {
                $params['PATIENT_AGE'] = Date\Helper::getAge($visit['PATIENT_BIRTHDAY']);
                unset($visit['PATIENT_BIRTHDAY']);

                $params = array_merge($params, $visit);
            }
        }

        return $params;
    }

}