<?

namespace Mmit\NewSmile\Rest\Entity;

use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\Application;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;
use Mmit\NewSmile\Notice;
use Mmit\NewSmile\VisitTable;

class Visit extends Controller
{
    protected function processNew()
    {
        try
        {
            $notice = new Notice\NewVisitRequest([
                'SERVICE_ID' => $this->getParam('service_id'),
                'DATE' => $this->getParam('date'),
                'NEAR_FUTURE' => $this->getParam('near_future') === 'true',
                'COMMENT' => $this->getParam('comment'),
                'PATIENT_ID' => Application::getInstance()->getUser()->getId()
            ]);

            $notice->push(['admin']);
        }
        catch(\Error $e)
        {
            $this->setError($e);
        }

    }

    protected function processChangeDate()
    {
        try
        {
            $notice = new Notice\VisitChangeDate([
                'VISIT_ID' => $this->getParam('id'),
                'NEW_DATE' => $this->getParam('new_date'),
                'PATIENT_ID' => Application::getInstance()->getUser()->getId()
            ]);

            $notice->push(['admin']);
        }
        catch (Error $e)
        {
            $this->setError($e);
        }
    }

    protected function processList()
    {
        $filter = [
            'PATIENT_ID' => Application::getInstance()->getUser()->getId()
        ];

        $isActive = $this->getParam('is_active');

        if(isset($isActive))
        {
            $filterKey = ($isActive == 'true' ? '>=' : '<') . 'TIME_END';
            $filter[$filterKey] = new DateTime();
        }

        $limit = $this->getParam('limit');
        $offset = $this->getParam('offset') ?: 0;

        $queryParams = [
            'filter' => $filter,
            'select' => [
                'ID',
                'TIME_START',
                'TIME_END',
                'DOCTOR_NAME' => 'DOCTOR.NAME',
                'DOCTOR_LAST_NAME' =>'DOCTOR.LAST_NAME',
                'DOCTOR_SECOND_NAME' =>'DOCTOR.SECOND_NAME',
                'STATUS'
            ],
            'offset' => $offset
        ];

        if($limit)
        {
            $queryParams['limit'] = $limit;
        }

        $statusesTitles = VisitTable::getEnumVariants('STATUS');

        $dbVisit = VisitTable::getList($queryParams);

        while($visit = $dbVisit->fetch())
        {
            $this->responseData[] = [
                'id' => $visit['ID'],
                'date' => $visit['TIME_START']->format('d.m.Y H:i:s'),
                'doctor' => Helpers::getFio($visit, 'DOCTOR_'),
                'is_active' => ($visit['TIME_END']->getTimestamp() >= time()),
                'status' => $statusesTitles[$visit['STATUS']]
            ];
        }
    }


    protected function getActionsMap()
    {
        return [
            'new' => [
                'PARAMS' => [
                    'service_id' => [
                        'TITLE' => 'id услуги'
                    ],
                    'date' => [
                        'TITLE' => 'желаемая дата приема'
                    ],
                    'near_future' => [
                        'TITLE' => 'флаг записи на ближайшее время'
                    ],
                    'comment' => [
                        'TITLE' => 'комментарий'
                    ]
                ]
            ],
            'change-date' => [
                'PARAMS' => [
                    'new_date' => [
                        'TITLE' => 'новая дата приема'
                    ],
                    'id' => [
                        'TITLE' => 'id приема'
                    ]
                ]
            ],
            'list' => [
                'PARAMS' => [
                    'offset' =>  [
                        'TITLE' => 'смещение выборки от начала',
                        'REQUIRED' => false
                    ],
                    'limit' => [
                        'TITLE' => 'ограничение количества',
                        'REQUIRED' => false
                    ],
                    'is_active' => [
                        'TITLE' => 'флаг выборки только будущих приемов',
                        'REQUIRED' => false
                    ]
                ]
            ]
        ];
    }

    protected function getDefaultAction()
    {
        return '';
    }
}