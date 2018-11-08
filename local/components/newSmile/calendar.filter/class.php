<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Type\DateTime,
    Bitrix\Main\Config\Option,
    Mmit\NewSmile\VisitTable,
    Mmit\NewSmile\ScheduleTable,
    Mmit\NewSmile\DoctorTable,
    Mmit\NewSmile\PatientCardTable,
    Mmit\NewSmile\WorkChairTable,
    \Mmit\NewSmile,
    Bitrix\Main\ORM\Query\Query,
    Bitrix\Main\Entity\ExpressionField,
    Bitrix\Main\DB;

class CalendarFilterComponent extends \CBitrixComponent
{
    private $FILTER_NAME = '';


    public function onPrepareComponentParams($arParams)
    {
        if(!($arParams['FILTER'] instanceof Bitrix\Main\ORM\Query\Filter\ConditionTree))
        {
            $arParams['FILTER'] = Query::filter();
        }

        return $arParams;
    }

    /**
	 * получение результатов
	 */
	protected function getResult()
	{
        //$this->getWorkChair();
        $this->getDoctors();
        $this->getSpecializations();
        $this->getTimeFilterInfo();
        //$this->getPatients();
	}

    protected function getTimeFilterInfo()
    {
        $this->arResult['START_TIME'] = NewSmile\Config::getScheduleStartTime();
        $this->arResult['END_TIME'] = NewSmile\Config::getScheduleEndTime();
    }

    protected function getSpecializations()
    {
       $this->arResult['SPECIALIZATIONS'] = NewSmile\DoctorSpecializationTable::getEnumVariants('SPECIALIZATION');
    }

    protected function getWorkChair()
    {
        $isResult = false;
        $rsWorkChair = WorkChairTable::getList([
            'filter' => [
                'CLINIC_ID' => NewSmile\Config::getClinicId()
            ]
        ]);
        while ($arWorkChair = $rsWorkChair->Fetch())
        {
            $this->arResult['WORK_CHAIR'][] = $arWorkChair;
            $isResult = true;
        }
        return $isResult;
    }

    protected function getDoctors()
    {
        $rsDoctor = DoctorTable::getList(array(
            'select' => array(
                'ID', 'NAME', 'COLOR', 'LAST_NAME', 'SECOND_NAME'
            ),
            'filter' => [
                'CLINIC_ID' => NewSmile\Config::getClinicId()
            ]
        ));
        while ($arDoctor = $rsDoctor->fetch())
        {
            $this->arResult['DOCTORS'][] = $arDoctor;
        }
    }

    protected function getPatients()
    {
        $rsPatient = PatientCardTable::getList(array(
            'select' => array(
                'ID', 'NAME'
            )
        ));
        while ($arPatient = $rsPatient->fetch())
        {
            $this->arResult['PATIENTS'][] = $arPatient['NAME'];
        }
    }

    protected function getFilter($request)
    {
        /**
         * @var \Bitrix\Main\ORM\Query\Filter\ConditionTree $filter
         */
        $filter = $this->arParams['FILTER'];

        if (!empty($request['TIME_FROM']))
        {
            $filter->where(
                new ExpressionField('TIME_SECONDS', 'TIME_TO_SEC(%s)','TIME'),
                '>=',
                new DB\SqlExpression('TIME_TO_SEC(?)', urldecode($request['TIME_FROM']))
            );
        }

        if (!empty($request['TIME_TO']))
        {
            $filter->where(
                new ExpressionField('TIME_SECONDS', 'TIME_TO_SEC(%s)','TIME'),
                '<',
                new DB\SqlExpression('TIME_TO_SEC(?)', urldecode($request['TIME_TO']))
            );
        }

        if (!empty($request['DOCTOR']))
        {
            $filter->where('DOCTOR_ID', $request['DOCTOR']);
        }

        if(!empty($request['SPEC']))
        {
            $specSubQuery = new Query(NewSmile\DoctorSpecializationTable::getEntity());
            $specSubQuery->setFilter(array(
                'SPECIALIZATION' => $request['SPEC']
            ));
            $specSubQuery->setSelect(array('DOCTOR_ID'));

            $filter->whereIn('DOCTOR_ID', $specSubQuery);
        }

        $filter->where('CLINIC_ID', NewSmile\Config::getClinicId());

        $this->arResult['CURRENT_FILTER'] = $filter;

        return $filter;
    }
	
	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
		try
		{
            if (!Loader::includeModule('mmit.newSmile')) die();
            if (empty($this->arParams['FILTER_NAME'])) {
                $this->FILTER_NAME = $this->arParams['FILTER_NAME'];
            } else {
                $this->FILTER_NAME = 'arFilter';
            }
			$this->getResult();
            $filter = $this->getFilter($this->request);

            $this->includeComponentTemplate();

            return $filter;
		}
		catch (Exception $e)
		{
			ShowError($e->getMessage());
		}
	}
}
?>