<?

namespace Mmit\NewSmile\Command;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Mmit\NewSmile\CommandVariable;
use Mmit\NewSmile\Error;
use Mmit\NewSmile\Helpers;

/**
 * Реализует команду - обёртку над методом getList ORM сущности.
 * Class OrmGetList
 * @package Mmit\NewSmile\Command
 */
abstract class OrmGetList extends OrmRead
{
    // TODO нужно обязать дочерние классы детально описывать схему доступа к полям сущности

    protected function doExecute()
    {
        $entity = $this->getOrmEntity();
        $dataManagerClass = $entity->getDataClass();

        $queryParams = $this->params;
        $queryParams['count_total'] = $queryParams['countTotal'];
        unset($queryParams['countTotal']);

        $queryParams['filter'] = $this->modifyFilter($queryParams['filter']);

        $dbRows = $dataManagerClass::getList($queryParams);

        $this->result = [];

        while($row = $dbRows->fetch())
        {
            $this->result['list'][] = $this->prepareRow($row);

            if($queryParams['count_total'])
            {
                $this->result['count'] = $dbRows->getCount();
            }
        }
    }

    /**
     * Модифицирует фильтр, изначально заданный через параметр команды filter
     * @param $filter
     *
     * @return mixed
     */
    protected function modifyFilter(array $filter)
    {
        return $filter;
    }

    protected function prepareParamValue($paramCode, $paramValue)
    {
        if(($paramCode == 'filter') || ($paramCode == 'order'))
        {
            $paramValue = $this->prepareFieldsArray($paramValue, true);
        }
        elseif($paramCode == 'select')
        {
            $paramValue = $this->prepareFieldsArray($paramValue);

            /* блокировка использования точки в select, тк этот механизм позволяет обойти систему контроля доступа */
            foreach ($paramValue as $item)
            {
                if(!preg_match('/^[A-Za-z0-9_]+$/', $item))
                {
                    throw new Error('Поля сущностей по ссылкам в select не поддерживаются', 'SELECT_LINKED_ENTITIES_NOT_SUPPORTED');
                }
            }
        }

        return $paramValue;
    }


    protected function prepareFieldsArray($array, $bKeys = false)
    {
        $entity = $this->getOrmEntity();
        $result = [];

        foreach ($array as $key => $value)
        {
            $rawFieldCode = ($bKeys ? $key : $value);
            $fieldCode = Helpers::getSnakeCase($rawFieldCode);

            if($entity->hasField($fieldCode))
            {
                if($bKeys)
                {
                    $result[$fieldCode] = $value;
                }
                else
                {
                    $result[] = $fieldCode;
                }
            }
            else
            {
                $this->sayBadFieldCode($rawFieldCode);
            }
        }

        return $result;
    }

    protected function sayBadFieldCode($fieldCode)
    {
        throw new Error('Поле с кодом ' . $fieldCode . ' не существует', 'BAD_FIELD_CODE');
    }

    public function getParamsMap()
    {
        return [
            new CommandVariable\Object('filter', 'фильтр', false, []),
            new CommandVariable\ArrayParam('select', 'поля для выборки'),
            new CommandVariable\Object('order', 'порядок сортировки'),
            new CommandVariable\Integer('limit', 'ограничение по количеству'),
            new CommandVariable\Integer('offset', 'смещение от начала выборки'),
            new CommandVariable\Bool('countTotal', 'флаг подсчета количества', false, false)
        ];
    }

    public function getResultFormat()
    {
        return new ResultFormat([
            new CommandVariable\Integer('count', 'общее количество записей (без учёта limit и offset)'),
            (new CommandVariable\ArrayParam('list', 'список записей', true))->setContentType(
                new CommandVariable\Object('', '')
            )
        ]);
    }
}