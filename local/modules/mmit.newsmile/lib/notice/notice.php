<?

namespace Mmit\NewSmile\Notice;

use Bitrix\Main\Loader;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Notice\Data\NoticeTable;
use Mmit\NewSmile\RoleTable;

abstract class Notice
{
    protected $params;

    public function __construct($params = [])
    {
        foreach ($this->getParamsList() as $paramName)
        {
            if(!isset($params[$paramName]))
            {
                throw new Error('Не указан параметр ' . $paramName . '  уведомления ' . $this->getType(), 'PARAM_NOT_SPECIFIED');
            }
        }

        $this->params = $params;
        $this->extendParams();
    }

    protected function getType()
    {
        $className = Helpers::getShortClassName(static::class);
        return Helpers::getSnakeCase($className);
    }

    public function push($users)
    {
        $noticeId = null;
        $this->prepareUserIdArray($users);

        $noticeData = array(
            'TYPE' => $this->getType(),
            'PARAMS' => $this->params
        );

        if(!$users)
        {
            $users[] = $GLOBALS['USER']->GetID();
        }

        foreach ($users as $userId)
        {
            $currentNoticeData = $noticeData;
            $currentNoticeData['USER_ID'] = $userId;

            $addResult = NoticeTable::add($currentNoticeData);

            if($addResult->isSuccess())
            {
                $this->send($addResult->getId(), $userId);
            }
        }
    }

    /**
     * Отправляет уведомление получателям
     * @param int $noticeId - id уведомления
     * @param int $userId - id пользователей-получателей
     *
     * @throws \Bitrix\Main\LoaderException
     */
    protected function send($noticeId, $userId)
    {
        if(!Loader::includeModule('pull')) return;

        \CPullStack::AddByUser($userId, array(
            'module_id' => 'mmit.newsmile',
            'command' => 'add_notice',
            'params' => array(
                'ID' => $noticeId
            )
        ));
    }

    /**
     * Заменяет в массиве идентификаторов пользователей названия ролей массивами id пользователей, состоящих в данных ролях.
     * @param array $users
     */
    protected function prepareUserIdArray(array &$users)
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

    protected function extendParams()
    {
        return;
    }

    abstract protected function getParamsList();
}