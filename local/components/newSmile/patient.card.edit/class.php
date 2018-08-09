<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Type\DateTime,
    Mmit\NewSmile\Status,
    Mmit\NewSmile\PatientCardTable,
    Mmit\NewSmile\DoctorTable;

class PatientCardEditComponent extends \CBitrixComponent
{

    protected function requestResult($request)
    {
        $arFields = array();
        if (!empty($request['NAME'])) {
            $arFields['NAME'] = $request['NAME'];
            $arFields['USER']['NAME'] = $request['NAME'];
        }
        if (!empty($request['LAST_NAME'])) {
            $arFields['LAST_NAME'] = $request['LAST_NAME'];
            $arFields['USER']['LAST_NAME'] = $request['LAST_NAME'];
        }
        if (!empty($request['SECOND_NAME'])) {
            $arFields['SECOND_NAME'] = $request['SECOND_NAME'];
            $arFields['USER']['SECOND_NAME'] = $request['SECOND_NAME'];
        }
        if (!empty($request['PERSONAL_BIRTHDAY'])) {
            $arFields['PERSONAL_BIRTHDAY'] = new Date($request['PERSONAL_BIRTHDAY'], 'Y-m-d');
        }
        if (!empty($request['PERSONAL_GENDER'])) {
            $arFields['PERSONAL_GENDER'] = $request['PERSONAL_GENDER'];
        }
        if (!empty($request['PERSONAL_PHONE'])) {
            $arFields['PERSONAL_PHONE'] = $request['PERSONAL_PHONE'];
            $arFields['USER']['PERSONAL_PHONE'] = $request['PERSONAL_PHONE'];
        }
        if (!empty($request['PERSONAL_MOBILE'])) {
            $arFields['PERSONAL_MOBILE'] = $request['PERSONAL_MOBILE'];
        }
        if (!empty($request['EMAIL'])) {
            $arFields['EMAIL'] = $request['EMAIL'];
            $arFields['USER']['EMAIL'] = $request['EMAIL'];
        }
        if (!empty($request['PERSONAL_CITY'])) {
            $arFields['PERSONAL_CITY'] = $request['PERSONAL_CITY'];
        }
        if (!empty($request['PERSONAL_ZIP'])) {
            $arFields['PERSONAL_ZIP'] = $request['PERSONAL_ZIP'];
        }
        if (!empty($request['PERSONAL_STREET'])) {
            $arFields['PERSONAL_STREET'] = $request['PERSONAL_STREET'];
        }
        if (!empty($request['PERSONAL_NOTES'])) {
            $arFields['PERSONAL_NOTES'] = $request['PERSONAL_NOTES'];
        }
        if (!empty($request['WORK_COMPANY'])) {
            $arFields['WORK_COMPANY'] = $request['WORK_COMPANY'];
        }
        if (!empty($request['WORK_POSITION'])) {
            $arFields['WORK_POSITION'] = $request['WORK_POSITION'];
        }
        if (intval($request['STATUS_ID'])) {
            $arFields['STATUS_ID'] = intval($request['STATUS_ID']);
        }
        if (!empty($request['NUMBER'])) {
            $arFields['NUMBER'] = $request['NUMBER'];
        }
        if (!empty($request['FIRST_PRICE'])) {
            $arFields['FIRST_PRICE'] = $request['FIRST_PRICE'];
        }
        if (!empty($request['FIRST_VISIT'])) {
            $arFields['FIRST_VISIT'] = new DateTime($request['FIRST_VISIT'], 'Y-m-d\TH:i');
        }
        if (!empty($request['REPRESENTATIVE'])) {
            $arFields['REPRESENTATIVE'] = $request['REPRESENTATIVE'];
        }
        if (!empty($request['PARENTS'])) {
            $arFields['PARENTS'] = $request['PARENTS'];
        }
        if (!empty($request['SMS_NOTICE'])) {
            $arFields['SMS_NOTICE'] = $request['SMS_NOTICE'];
        }
        if (!empty($request['COMMENT'])) {
            $arFields['COMMENT'] = $request['COMMENT'];
        }
        if (!empty($request['DOCTORS_ID'])) {
            $arFields['DOCTORS_ID'] = $request['DOCTORS_ID'];
        }
        if (!empty($request['NEED_CHECK'])) {
            $arFields['NEED_CHECK'] = $request['NEED_CHECK'];
        }
        if (!empty($request['PASSPORT_SN'])) {
            $arFields['PASSPORT_SN'] = $request['PASSPORT_SN'];
        }
        if (!empty($request['PASSPORT_ISSUED_BY'])) {
            $arFields['PASSPORT_ISSUED_BY'] = $request['PASSPORT_ISSUED_BY'];
        }
        if (!empty($request['PASSPORT_ISSUED_DATE'])) {
            $arFields['PASSPORT_ISSUED_DATE'] = new Date($request['PASSPORT_ISSUED_DATE'], 'Y-m-d');
        }
        if (!empty($request['PASSPORT_PLACE_BIRTH'])) {
            $arFields['PASSPORT_PLACE_BIRTH'] = $request['PASSPORT_PLACE_BIRTH'];
        }
        if (!empty($request['PASSPORT_ADDRESS'])) {
            $arFields['PASSPORT_ADDRESS'] = $request['PASSPORT_ADDRESS'];
        }
        if (!empty($request['PASSPORT_ADDRESS_DATE'])) {
            $arFields['PASSPORT_ADDRESS_DATE'] = new Date($request['PASSPORT_ADDRESS_DATE'], 'Y-m-d');
        }
        if (!empty($request['PASSPORT_OTHER'])) {
            $arFields['PASSPORT_OTHER'] = $request['PASSPORT_OTHER'];
        }
        if (!empty($request['SOURCE'])) {
            $arFields['SOURCE'] = $request['SOURCE'];
        }
        if (!empty($request['ARCHIVE'])) {
            $arFields['ARCHIVE'] = $request['ARCHIVE'];
        }
        if (!empty($request['FAMILY_ID'])) {
            $arFields['FAMILY_ID'] = $request['FAMILY_ID'];
        }
        if (!empty($request['JOINT_ACCOUNT'])) {
            $arFields['JOINT_ACCOUNT'] = $request['JOINT_ACCOUNT'];
        }
        if (!empty($request['USER'])) {
            $arFields['USER'] = $request['USER'];
        }

        if (!empty($arFields['USER']) && is_array($arFields['USER'])) {
//            if (!empty($arFields['USER']['PERSONAL_BIRTHDAY'])) {
//                $arFields['USER']['PERSONAL_BIRTHDAY'] = new Date($arFields['USER']['PERSONAL_BIRTHDAY'], 'Y-m-d');
//            }
//            $userID = PatientCardTable::getUserIDByID($this->arParams['ID']);
//            $user = new CUser();
//            $user->Update($userID, $arFields['USER']);
            unset($arFields['USER']);
        }
        if (!empty($arFields)) {
            PatientCardTable::update($this->arParams['ID'], $arFields);
        }
    }

	/**
	 * получение результатов
	 */
	protected function getResult()
	{
        $rsResult = PatientCardTable::getList([
            'filter' => [
                'ID' => $this->arParams['ID']
            ]
        ]);
        if ($arResult = $rsResult->fetch()) {
            $this->arResult['PATIENT_CARD'] = $arResult;
        }

        if($this->arResult['PATIENT_CARD']['FIRST_VISIT']) {
            $this->arResult['PATIENT_CARD']['FIRST_VISIT'] = $this->arResult['PATIENT_CARD']['FIRST_VISIT']->format('Y-m-d\TH:i');
        }
        if($this->arResult['PATIENT_CARD']['PASSPORT_ISSUED_DATE']) {
            $this->arResult['PATIENT_CARD']['PASSPORT_ISSUED_DATE'] = $this->arResult['PATIENT_CARD']['PASSPORT_ISSUED_DATE']->format('Y-m-d');
        }
        if($this->arResult['PATIENT_CARD']['PASSPORT_ADDRESS_DATE']) {
            $this->arResult['PATIENT_CARD']['PASSPORT_ADDRESS_DATE'] = $this->arResult['PATIENT_CARD']['PASSPORT_ADDRESS_DATE']->format('Y-m-d');
        }
        if($this->arResult['PATIENT_CARD']['USER_PERSONAL_BIRTHDAY']) {
            $this->arResult['PATIENT_CARD']['USER_PERSONAL_BIRTHDAY'] = $this->arResult['PATIENT_CARD']['USER_PERSONAL_BIRTHDAY']->format('Y-m-d');
        }

        $this->getDoctors();
        $this->getStatusPatient();
	}

	protected function getStatusPatient()
    {
        $rsStatusPatient = Status\PatientTable::getList(array(
            'select' => array('ID', 'NAME')
        ));
        while ($arStatusPatient = $rsStatusPatient->fetch())
        {
            $this->arResult['STATUS_PATIENT'][] = $arStatusPatient;
        }
    }

    protected function getDoctors()
    {
        $rsDoctor = DoctorTable::getList(array(
            'filter' => [
                'CLINIC_ID' => $_SESSION['CLINIC_ID']
            ],
            'select' => array('ID','NAME')
        ));
        while ($arDoctor = $rsDoctor->fetch())
        {
            $this->arResult['DOCTORS'][] = $arDoctor;
        }
    }

	/**
	 * выполняет логику работы компонента
	 */
	public function executeComponent()
	{
        if (!Loader::includeModule('mmit.newSmile')) die();
		try
		{
		    $this->requestResult($this->request);
			$this->getResult();
			$this->includeComponentTemplate();
		}
		catch (Exception $e)
		{
			ShowError($e->getMessage());
		}
	}
}
?>