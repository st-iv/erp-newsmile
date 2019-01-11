<?
namespace Mmit\NewSmile\Notice\Data;

use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\PatientCardTable;
use Mmit\NewSmile\Access\RoleTable;
use Mmit\NewSmile\Service\ServiceTable;
use Mmit\NewSmile\Visit\VisitTable;
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
    public static function extendNoticeDataByType(array &$noticeData)
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
     * @param array $users - id пользователей-получателей. Можно указывать как конкретные id, так и коды ролей
     * @param array $params - параметры уведомления
     * @param bool $bUseParamModificator - использовать модификатор параметров
     */
    public static function push($typeCode, array $params = array(), array $users = array(), $bUseParamModificator = true)
    {
        $sendToUsers = array();
        $noticeId = null;
        static::prepareUserIdArray($users);

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
     * Заменяет в массиве идентификаторов пользователей названия ролей массивами id пользователей, состоящих в данных ролях.
     * @param array $users
     */
    protected static function prepareUserIdArray(array &$users)
    {
        foreach ($users as $index => $userIndex)
        {
            if(is_string($userIndex))
            {
                $roleUsers = RoleTable::getUsersByRole($userIndex, [
                    'select' => ['ID']
                ]);

                unset($users[$index]);

                $users = array_merge($users, array_keys($roleUsers));
            }
        }

        $users = array_unique($users);
    }


    protected static function modifyParamsWaitingListSuggest($params)
    {
        if($params['PATIENT_ID'])
        {
            $dbPatient = PatientCardTable::getByPrimary($params['PATIENT_ID'], array(
                'select' => ['NAME', 'LAST_NAME', 'SECOND_NAME', 'PERSONAL_PHONE']
            ));

            if($patient = $dbPatient->fetch())
            {
                $params['PATIENT_PHONE'] = $patient['PERSONAL_PHONE'];
                $params['PATIENT_FIO'] = Helpers::getFio($patient);
            }
        }

        return $params;
    }

    protected static function modifyParamsNewVisitRequest($params)
    {
        if($params['SERVICE_ID'])
        {
            $dbService = ServiceTable::getByPrimary($params['SERVICE_ID'], [
                'select' => ['NAME']
            ]);

            if($service = $dbService->fetch())
            {
                $params['SERVICE'] = $service['NAME'];
            }
        }

        if($params['PATIENT_ID'])
        {
            $dbPatient = PatientCardTable::getByPrimary($params['PATIENT_ID'], [
                'select' => ['LAST_NAME', 'NAME', 'SECOND_NAME']
            ]);

            if($patient = $dbPatient->fetch())
            {
                $params['PATIENT'] = Helpers::getFio($patient);
            }
        }

        if($params['NEAR_FUTURE'])
        {
            $params['DATE'] = 'ближайшее время';
        }

        return $params;
    }

    protected static function modifyParamsVisitChangeDate($params)
    {
        if($params['PATIENT_ID'])
        {
            $dbPatient = PatientCardTable::getByPrimary($params['PATIENT_ID'], [
                'select' => ['LAST_NAME', 'NAME', 'SECOND_NAME']
            ]);

            if($patient = $dbPatient->fetch())
            {
                $params['PATIENT'] = Helpers::getFio($patient);
            }
        }

        if($params['VISIT_ID'])
        {
            $dbVisit = VisitTable::getByPrimary($params['VISIT_ID'], [
                'select' => ['TIME_START'],
                'filter' => [
                    'PATIENT_ID' => $params['PATIENT_ID']
                ]
            ]);

            if($visit = $dbVisit->fetch())
            {
                $params['ACTUAL_DATE'] = $visit['TIME_START'];
            }
        }

        return $params;
    }

}