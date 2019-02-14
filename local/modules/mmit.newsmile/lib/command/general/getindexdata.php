<?


namespace Mmit\NewSmile\Command\General;

use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Command;

class GetIndexData extends Base
{
    public function getDescription()
    {
        return 'Возвращает данные для отображения на главной странице erp системы (расписание, уведомления, поиск)';
    }

    protected function doExecute()
    {
        $commandCalendar = new Command\Schedule\GetCalendar();
        $commandCalendar->execute();

        $commandDaysInfo = new Command\Schedule\GetDaysInfo();
        $commandDaysInfo->execute();

        $commandDoctorsList = new Command\Doctor\GetListMobile([
            'select' => ['ID', 'NAME', 'COLOR', 'LAST_NAME', 'SECOND_NAME'],
            'get-specialization' => true
        ]);
        $commandDoctorsList->execute();

        $commandNoticeList = new Command\Notice\GetList([
            'limit' => 50,
            'order' => [
                'id' => 'desc'
            ],
            'countTotal' => true
        ]);
        $commandNoticeList->execute();

        $commandNoticeGroupList = new Command\Notice\GetGroupList();
        $commandNoticeGroupList->execute();

        $this->result = [
            'calendar' => [
                'colorsScheme' => [
                    0 => array(
                        'background' => '454545',
                        'text' => 'fff'
                    ),
                    30 => array(
                        'background' => 'ff3758',
                        'text' => 'fff'
                    ),
                    90 => array(
                        'background' => 'ffb637',
                        'text' => 'fff'
                    ),
                    150 => array(
                        'background' => 'eaed14',
                    ),
                    270 => array(
                        'background' => '73cc00',
                        'text' => 'fff'
                    )
                ],
                'data' => $commandCalendar->getResult()
            ],
            'schedule' => $commandDaysInfo->getResult(),
            'initialDate' => date('Y-m-d'),

            'doctors' => $commandDoctorsList->getResult(),

            'notices' => [
                'noticeList' => $commandNoticeList->getResult(),
                'noticeGroupList' => $commandNoticeGroupList->getResult()
            ],

            'search' => [
                'useLanguageGuess' => true,
                'minQueryLength' => 3,
                'topCount' => 200
            ]
        ];
    }

    public function getParamsMap()
    {
        return [];
    }
}