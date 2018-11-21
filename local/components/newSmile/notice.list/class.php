<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Mmit\NewSmile\Notice;

class NewSmileNoticeList extends \CBitrixComponent
{
    protected $filter = array();

    protected function getNotices()
    {
        \Bitrix\Main\Loader::includeModule('mmit.newsmile');

        $filter = $this->filter;
        $filter['USER_ID'] = $GLOBALS['USER']->GetID();

        $dbNotices = Notice\Data\NoticeTable::getList(array(
            'filter' => $filter,
            'order' => array(
                'ID' => 'desc'
            )
        ));

        $notices = array();

        while($notice = $dbNotices->fetch())
        {
            Notice\Data\NoticeTable::extendNoticeDataByType($notice);
            $notices[$notice['ID']] = $notice;
        }

        return $notices;
    }

    protected function getNoticesGroups()
    {
        return Notice\Data\TypeTable::getEnumVariants('GROUP');
    }

    protected function processRequest(\Bitrix\Main\HttpRequest $request)
    {
        if(check_bitrix_sessid())
        {
            if($request['del_notices'])
            {
                $this->deleteNotices(explode(',', $request['del_notices']));
            }

            if($request['read_notices'])
            {
                $this->readNotices( explode(',', $request['read_notices']) );
            }

            if($request['notices_ids'])
            {
                $this->filter['ID'] = explode(',', $request['notices_ids']);
            }
        }
    }

    protected function deleteNotices($noticesIds)
    {
        $this->arResult['DELETED_ITEMS'] = array();

        $dbNotices = Notice\Data\NoticeTable::getList(array(
            'filter' => array(
                'ID' => $noticesIds,
                'USER_ID' => $GLOBALS['USER']->GetID()
            ),
            'select' => array('ID')
        ));

        while($notice = $dbNotices->fetch())
        {
            Notice\Data\NoticeTable::delete($notice['ID']);
        }
    }

    protected function readNotices($noticesIds)
    {
        $dbNotices = Notice\Data\NoticeTable::getList(array(
            'filter' => array(
                'ID' => $noticesIds,
                'USER_ID' => $GLOBALS['USER']->GetID(),
                'IS_READ' => false
            ),
            'select' => array('ID')
        ));

        while($notice = $dbNotices->fetch())
        {
            Notice\Data\NoticeTable::update($notice['ID'], array(
                'IS_READ' => true
            ));
        }
    }

    public function executeComponent()
    {
        try
        {
            if($this->request->isPost())
            {
                $this->processRequest($this->request);
            }

            $this->arResult['NOTICES'] = $this->getNotices();
            $this->arResult['GROUPS'] = $this->getNoticesGroups();
            $this->includeComponentTemplate();
        }
        catch (\Exception $e)
        {
            ShowError($e->getMessage());
        }
    }
}