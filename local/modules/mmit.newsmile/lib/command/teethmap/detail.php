<?


namespace Mmit\NewSmile\Command\TeethMap;

use Mmit\NewSmile\Application;
use Mmit\NewSmile\Command\Base;
use Mmit\NewSmile\Command\ResultFormat;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\PatientCardTable;
use Mmit\NewSmile\Status\ToothTable;
use Mmit\NewSmile\CommandVariable;

class Detail extends Base
{
    public function getDescription()
    {
        return 'Получает детальную информацию по карте зубов текущего пользователя';
    }

    public function getResultFormat()
    {
        return new ResultFormat([
            (new CommandVariable\Object('<код челюсти lower/upper>', 'карта зубов определённой челюсти'))->setShape([
                (new CommandVariable\Object('<код возраста child/parent>', 'карта взрослых / детских зубов'))->setShape([
                    new CommandVariable\Integer('number', 'номер зуба', true),
                    new CommandVariable\String('status', 'код статуса зуба', true),
                    new CommandVariable\String('status_decode', 'расшифровка статуса зуба', true),
                    new CommandVariable\String('status_group', 'код группы статуса зуба', true),
                    new CommandVariable\String('status_group_decode', 'расшифровка группы статуса зуба', true),
                ])
            ]),
            (new CommandVariable\ArrayParam('status_list', 'полный список статусов зубов', true))->setContentType(
                (new CommandVariable\Object('', ''))->setShape([
                    new CommandVariable\Integer('id', 'id статуса', true),
                    new CommandVariable\String('code', 'код статуса', true),
                    new CommandVariable\String('decode', 'расшифровка статуса', true),
                ])
            ),
            (new CommandVariable\ArrayParam('status_group_list', 'список групп статусов', true))->setContentType(
                (new CommandVariable\Object('', ''))->setShape([
                    new CommandVariable\String('code', 'код группы статусов', true),
                    new CommandVariable\String('decode', 'расшифровка группы статусов', true),
                ])
            )
        ]);
    }

    protected function doExecute()
    {
        $user = Application::getInstance()->getUser();
        if(!$user->is('patient'))
        {
            throw new Error('Команда предназначения для выполнения от имени пациента, текущий пользователь не является пациентом', 'USER_IS_NOT_PATIENT');
        }

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

            $this->result[$jawCode][$ageCode][] = [
                'number' => $toothNumber,
                'status' => $status['CODE'],
                'status_decode' => $status['NAME'],
                'status_group' => $status['GROUP'],
                'status_group_decode' => $statusesGroupsNames[$status['GROUP']],
            ];
        }

        $this->result['status_list'] = [];

        /* полный список статусов зубов */
        foreach ($toothStatuses as $toothStatus)
        {
            $this->result['status_list'][] = [
                'id' => $toothStatus['ID'],
                'code' => $toothStatus['CODE'],
                'decode' => $toothStatus['NAME']
            ];
        }

        /* полный список групп статусов зубов */
        foreach ($statusesGroupsNames as $groupCode => $groupName)
        {
            $this->result['status_group_list'][] = [
                'code' => $groupCode,
                'decode' => $groupName
            ];
        }
    }

    public function getParamsMap()
    {
        return [];
    }
}