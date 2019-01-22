<?

namespace Mmit\NewSmile\Command\VisitRequest;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Service\ServiceTable;
use Mmit\NewSmile\Visit\VisitRequestTable;

class GetListMobile extends GetList
{
    protected function doExecute()
    {
        $this->params['filter']['paitentId'] = Application::getInstance()->getUser()->getId();

        parent::doExecute();

        $statusesTitles = VisitRequestTable::getEnumVariants('STATUS');

        foreach($this->result['list'] as &$visitRequest)
        {
            $visitRequestInfo = [
                'id' => $visitRequest['id'],
                'date' => $visitRequest['date'],
                'doctor' => $visitRequest['doctorId'],
                'is_active' => ($visitRequest['status'] == 'WAITING'),
                'status_code' => $visitRequest['status'],
                'status' => $statusesTitles[$visitRequest['status']],
                'is_near_future' => ($visitRequest['nearFuture'] == true),
            ];

            if($visitRequest['serviceId'])
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

            $result[] = $visitRequestInfo;







            /*$visitRequestInfo = [
                'id' => $visitRequest['ID'],
                'date' => $visitRequest['DATE'] ? $visitRequest['DATE']->format('d.m.Y H:i:s') : null,
                'doctor' => $visitRequest['DOCTOR_ID'],
                'is_active' => $visitRequest['STATUS'] == 'WAITING',
                'status' => $statusesTitles[$visitRequest['STATUS']],
                'status_code' => $visitRequest['STATUS'],
                'is_visit_request' => true,  ///!!
                'is_near_future' => $visitRequest['NEAR_FUTURE'] == true,
                'is_date_change_queried' => null, //!!!
                'new_date' => null, //!!
                'timestamp' => $visitRequest['DATE'] ? $visitRequest['DATE']->getTimestamp() : null, //!
                'create_timestamp' => $visitRequest['DATE_CREATE']->getTimestamp(), //!!
                'date_create' => $visitRequest['DATE_CREATE']->format('d.m.Y H:i:s'), //!!
            ];*/

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

            $result[] = $visitRequestInfo;
        }
    }

    protected function getServices($visitRequests)
    {
        $serviceIds = [];

        foreach ($visitRequests as $visitRequest)
        {
            if($visitRequest['serviceId'])
            {
                $serviceIds[] = $serviceIds;
            }
        }

        $dbServices = ServiceTable::getList([
            'filter' => [
                'ID' => $serviceIds
            ],
            'select' =>
        ]);

        $result = [];

        while($service = $dbServices->fetch())
        {
            $result[$service['ID']] =
        }
    }
}