<?


namespace Mmit\NewSmile\Rest\Entity;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\PatientCardTable;
use Mmit\NewSmile\Status\ToothTable;

class TeethMap extends Controller
{
    protected function processDetail()
    {
        $dbPatientCard = PatientCardTable::getByPrimary(Application::getInstance()->getUser()->getId(), [
            'select' => ['TEETH_MAP']
        ]);

        $patient = $dbPatientCard->fetch();

        $dbToothStatuses = ToothTable::getList();
        $toothStatuses = [];
        $upperJawTooth = array_flip([1,2,5,6]);

        while($toothStatus = $dbToothStatuses->fetch())
        {
            $toothStatuses[$toothStatus['ID']] = $toothStatus;
        }

        $statusesGroupsNames = ToothTable::getEnumVariants('GROUP');

        /* информация о зубах пациента */
        foreach ($patient['TEETH_MAP'] as $toothNumber => $statusId)
        {
            $status = $toothStatuses[$statusId];
            $toothGroupNum = (int)floor($toothNumber / 10);

            $jawCode = (isset($upperJawTooth[$toothGroupNum]) ? 'upper' : 'lower') . '_jaw';
            $ageCode = (($toothGroupNum > 4) ? 'child' : 'parent');

            $this->responseData[$jawCode][$ageCode][] = [
                'number' => $toothNumber,
                'status' => $status['CODE'],
                'status_decode' => $status['NAME'],
                'status_group' => $status['GROUP'],
                'status_group_decode' => $statusesGroupsNames[$status['GROUP']],
            ];
        }

        /* полный список статусов зубов */
        foreach ($toothStatuses as $toothStatus)
        {
            $this->responseData['status_list'][] = [
                'id' => $toothStatus['ID'],
                'code' => $toothStatus['CODE'],
                'decode' => $toothStatus['NAME']
            ];
        }

        /* полный список групп статусов зубов */
        foreach ($statusesGroupsNames as $groupCode => $groupName)
        {
            $this->responseData['status_group_list'][] = [
                'code' => $groupCode,
                'decode' => $groupName
            ];
        }
    }

    protected function processStatusList()
    {
        $dbToothStatuses = ToothTable::getList();

        while($status = $dbToothStatuses->fetch())
        {
            $this->responseData['status_list'][] = [
                'id' => $status['ID'],
                'code' => $status['CODE'],
                'decode' => $status['NAME']
            ];
        }
    }

    protected function processStatusGroupList()
    {
        $groups = ToothTable::getEnumVariants('GROUP');

        foreach ($groups as $code => $name)
        {
            $this->responseData['status_group_list'][] = [
                'code' => $code,
                'decode' => $name,
            ];
        }
    }

    protected function getActionsMap()
    {
        return [
            'detail' => [],
            'status-list' => [],
            'status-group-list' => [],
        ];
    }
}