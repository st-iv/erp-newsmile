<?
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Mmit\NewSmile;

Loc::loadMessages(__FILE__);
class mmit_newsmile extends CModule
{
    var $MODULE_ID = 'mmit.newsmile';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;

    public function __construct()
    {
        $arModuleVersion = array();
        include(__DIR__.'/version.php');

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
        
        $this->MODULE_ID = 'mmit.newsmile';
        $this->MODULE_NAME = Loc::getMessage('BEX_D7DULL_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('BEX_D7DULL_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = Loc::getMessage('BEX_D7DULL_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'https://mmit.ru';
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->installDB();
    }

    public function doUninstall()
    {
        $this->uninstallDB();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function installDB()
    {
        $this->installDBUser();
        if (Loader::includeModule($this->MODULE_ID))
        {
            NewSmile\VisitTable::getEntity()->createDbTable();
            NewSmile\InvoiceTable::getEntity()->createDbTable();
            NewSmile\InvoiceItemTable::getEntity()->createDbTable();
            NewSmile\Status\VisitTable::getEntity()->createDbTable();
            NewSmile\ClinicTable::getEntity()->createDbTable();
            NewSmile\DoctorTable::getEntity()->createDbTable();
            NewSmile\PatientCardTable::getEntity()->createDbTable();
            NewSmile\Status\PatientTable::getEntity()->createDbTable();
            NewSmile\TreatmentPlanTable::getEntity()->createDbTable();
            NewSmile\TreatmentPlanItemTable::getEntity()->createDbTable();
            NewSmile\ScheduleTable::getEntity()->createDbTable();
            NewSmile\ScheduleTemplateTable::getEntity()->createDbTable();
            NewSmile\WorkChairTable::getEntity()->createDbTable();
            NewSmile\WaitingListTable::getEntity()->createDbTable();

            NewSmile\VisitTable::createStatus();
            $this->testInstallDB();
        }
    }

    private function installDBUser()
    {
        $rsTypeEntity = CUserTypeEntity::GetList(
            [],
            [
                'ENTITY_ID' => 'USER',
                'FIELD_NAME' => 'UF_CLINIC',
            ]
        );
        if ($arTypeEntity = $rsTypeEntity->Fetch()) {

        } else {
            $oTypeEntity    = new CUserTypeEntity();
            $arField = [
                'ENTITY_ID' => 'USER',
                'FIELD_NAME' => 'UF_CLINIC',
                'USER_TYPE_ID' => 'integer',
                'XML_ID' => 'UF_CLINIC',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'Y',
                'EDIT_FORM_LABEL'   => array(
                    'ru' => 'Клиника',
                    'en' => 'Clinic',
                ),
                'LIST_COLUMN_LABEL' => array(
                    'ru' => 'Клиника',
                    'en' => 'Clinic',
                ),
                'LIST_FILTER_LABEL' => array(
                    'ru' => 'Клиника',
                    'en' => 'Clinic',
                ),
            ];
            $iUserFieldId = $oTypeEntity->Add($arField);
        }
    }

    public function uninstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID))
        {
            $connection = Application::getInstance()->getConnection();
            $connection->dropTable(NewSmile\VisitTable::getTableName());
            $connection->dropTable(NewSmile\InvoiceTable::getTableName());
            $connection->dropTable(NewSmile\InvoiceItemTable::getTableName());
            $connection->dropTable(NewSmile\Status\VisitTable::getTableName());
            $connection->dropTable(NewSmile\ClinicTable::getTableName());
            $connection->dropTable(NewSmile\DoctorTable::getTableName());
            $connection->dropTable(NewSmile\PatientCardTable::getTableName());
            $connection->dropTable(NewSmile\Status\PatientTable::getTableName());
            $connection->dropTable(NewSmile\TreatmentPlanTable::getTableName());
            $connection->dropTable(NewSmile\TreatmentPlanItemTable::getTableName());
            $connection->dropTable(NewSmile\ScheduleTable::getTableName());
            $connection->dropTable(NewSmile\ScheduleTemplateTable::getTableName());
            $connection->dropTable(NewSmile\WorkChairTable::getTableName());
            $connection->dropTable(NewSmile\WaitingListTable::getTableName());
        }
    }
    public function testInstallDB()
    {
        $xmlClinic = simplexml_load_file(__DIR__ . '/xml/clinic.xml');
        foreach ($xmlClinic->Value as $value)
        {
            NewSmile\ClinicTable::add([
                'NAME' => $value->NAME
            ]);
        }
        $xmlDoctor = simplexml_load_file(__DIR__ . '/xml/doctor.xml');
        foreach ($xmlDoctor->Value as $value)
        {
            NewSmile\DoctorTable::add([
                'NAME' => $value->NAME,
                'COLOR' => $value->COLOR,
                'USER_ID' => $value->USER_ID,
                'CLINIC_ID' => $value->CLINIC_ID
            ]);
        }
        $xmlStatusPatient = simplexml_load_file(__DIR__ . '/xml/statuspatient.xml');
        foreach ($xmlStatusPatient->Value as $value)
        {
            NewSmile\Status\PatientTable::add([
                'NAME' => $value->NAME
            ]);
        }
        $xmlPatient = simplexml_load_file(__DIR__ . '/xml/patientcard.xml');
        foreach ($xmlPatient->Value as $value)
        {
            NewSmile\PatientCardTable::add([
                'NAME' => $value->NAME,
                'STATUS_ID' => $value->STATUS_ID,
                'USER_ID' => $value->USER_ID,
            ]);
        }
        $xmlWorkChair = simplexml_load_file(__DIR__ . '/xml/workchair.xml');
        foreach ($xmlWorkChair->Value as $value)
        {
            NewSmile\WorkChairTable::add([
                'NAME' => $value->NAME,
                'CLINIC_ID' => $value->CLINIC_ID,
            ]);
        }

        NewSmile\ScheduleTemplateTable::addWeekSchedule(1);
        NewSmile\ScheduleTable::addWeekSchedule(date('Y-m-d'), 1);
        NewSmile\ScheduleTemplateTable::addWeekSchedule(2);
        NewSmile\ScheduleTable::addWeekSchedule(date('Y-m-d'), 2);


    }
}
