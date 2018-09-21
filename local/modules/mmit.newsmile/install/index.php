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
        $this->installAgents();
    }

    public function doUninstall()
    {
        $this->uninstallAgents();
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
            NewSmile\Status\ToothTable::getEntity()->createDbTable();
            NewSmile\FileTable::getEntity()->createDbTable();
            NewSmile\MeasureUnitTable::getEntity()->createDbTable();
            NewSmile\MaterialGroupTable::getEntity()->createDbTable();
            NewSmile\MaterialTable::getEntity()->createDbTable();
            NewSmile\StoreTable::getEntity()->createDbTable();
            NewSmile\MaterialQuantityTable::getEntity()->createDbTable();
            NewSmile\PackingListTable::getEntity()->createDbTable();
            NewSmile\PackingListItemTable::getEntity()->createDbTable();
            NewSmile\Service\ServiceTable::getEntity()->createDbTable();
            NewSmile\Service\GroupTable::getEntity()->createDbTable();
            NewSmile\Service\PriceTable::getEntity()->createDbTable();
            NewSmile\Service\PriceHistoryTable::getEntity()->createDbTable();

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
        $xmlPatient = simplexml_load_file(__DIR__ . '/xml/patientcard.xml');
        foreach ($xmlPatient->Value as $value)
        {
            NewSmile\PatientCardTable::add([
                'LAST_NAME' => $value->LAST_NAME,
                'NAME' => $value->NAME,
                'SECOND_NAME' => $value->SECOND_NAME,
                'STATUS_ID' => $value->STATUS_ID,
                'USER_ID' => $value->USER_ID,
                'PERSONAL_PHONE' => $value->PERSONAL_PHONE,
                'EMAIL' => $value->EMAIL
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
        $xmlStatusTooth = simplexml_load_file(__DIR__ . '/xml/statustooth.xml');
        foreach ($xmlStatusTooth->Value as $value)
        {
            NewSmile\Status\ToothTable::add([
                'NAME' => $value->NAME,
                'CODE' => $value->CODE
            ]);
        }

        NewSmile\ScheduleTemplateTable::addWeekSchedule(1);
        NewSmile\ScheduleTable::addWeekSchedule(date('Y-m-d'), 1);
        NewSmile\ScheduleTemplateTable::addWeekSchedule(2);
        NewSmile\ScheduleTable::addWeekSchedule(date('Y-m-d'), 2);


    }

    public function installAgents()
    {
        CAgent::AddAgent(
            "\Mmit\NewSmile\ScheduleTable::agentAddWeekSchedule('".date('d.m.Y', strtotime('+1 months'))."');",
            $this->MODULE_ID,
            "N",
            604800, // интервал запуска - 1 неделя
            date('d.m.Y H:i:s'),
            "Y",
            date('d.m.Y H:i:s'),
            30);
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
            $connection->dropTable(NewSmile\Status\ToothTable::getTableName());
            $connection->dropTable(NewSmile\FileTable::getTableName());
            $connection->dropTable(NewSmile\MeasureUnitTable::getTableName());
            $connection->dropTable(NewSmile\MaterialGroupTable::getTableName());
            $connection->dropTable(NewSmile\MaterialTable::getTableName());
            $connection->dropTable(NewSmile\StoreTable::getTableName());
            $connection->dropTable(NewSmile\MaterialQuantityTable::getTableName());
            $connection->dropTable(NewSmile\PackingListTable::getTableName());
            $connection->dropTable(NewSmile\PackingListItemTable::getTableName());
            $connection->dropTable(NewSmile\Service\ServiceTable::getTableName());
            $connection->dropTable(NewSmile\Service\GroupTable::getTableName());
            $connection->dropTable(NewSmile\Service\PriceTable::getTableName());
            $connection->dropTable(NewSmile\Service\PriceHistoryTable::getTableName());
        }
    }

    public function uninstallAgents()
    {
        CAgent::RemoveModuleAgents($this->MODULE_ID);
    }
}
