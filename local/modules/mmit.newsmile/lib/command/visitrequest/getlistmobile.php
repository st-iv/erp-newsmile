<?

namespace Mmit\NewSmile\Command\VisitRequest;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Command\Visit\GetUnitedList;
use Mmit\NewSmile;

class GetListMobile extends Base
{
    protected function doExecute()
    {
        $filter = [
            'PATIENT_ID' => Application::getInstance()->getUser()->getId()
        ];

        if(isset($this->params['is_active']))
        {
            $filter['STATUS'] = ($this->params['is_active'] ? 'WAITING' : 'CANCELED');
        }

        $dbVisitRequests = NewSmile\Visit\VisitRequestTable::getList([
            'filter' => $filter,
            'select' => [
                '*',
                'SERVICE_NAME' => 'SERVICE.NAME'
            ]
        ]);

        $statusesTitles = NewSmile\Visit\VisitRequestTable::getEnumVariants('STATUS');
        $doctorsIds = [];

        while($visitRequest = $dbVisitRequests->fetch())
        {
            $visitRequestInfo = [
                'id' => $visitRequest['ID'],
                'date' => $visitRequest['DATE'] ? $visitRequest['DATE']->format('d.m.Y H:i:s') : null,
                'is_active' => $visitRequest['STATUS'] == 'WAITING',
                'status' => $statusesTitles[$visitRequest['STATUS']],
                'doctor' => $visitRequest['DOCTOR_ID'],
                'status_code' => $visitRequest['STATUS'],
                'is_near_future' => $visitRequest['NEAR_FUTURE'] == true,
                'timestamp' => $visitRequest['DATE'] ? $visitRequest['DATE']->getTimestamp() : null,
                'create_timestamp' => $visitRequest['DATE_CREATE']->getTimestamp(),
                'date_create' => $visitRequest['DATE_CREATE']->format('d.m.Y H:i:s'),
            ];

            if($visitRequest['SERVICE_ID'])
            {
                $visitRequestInfo['service'] = [
                    'id' => $visitRequest['SERVICE_ID'],
                    'name' => $visitRequest['SERVICE_NAME']
                ];
            }
            else
            {
                $visitRequestInfo['service'] = null;
            }

            $this->result['list'][] = $visitRequestInfo;
            $doctorsIds[] = $visitRequest['DOCTOR_ID'];
        }

        /* информация о врачах */

        $doctors = NewSmile\Command\Visit\GetListMobile::getDoctors($doctorsIds);

        foreach ($this->result['list'] as &$visitRequest)
        {
            $visitRequest['doctor'] = $doctors[$visitRequest['doctor']];
        }

        unset($visitRequest);
    }

    public function getParamsMap()
    {
        return [
            GetUnitedList::getParam('is_active'),
            new NewSmile\CommandParam\ArrayParam('ids', 'список id запрашиваемых заявок на приём', '', false, [])
        ];
    }
}