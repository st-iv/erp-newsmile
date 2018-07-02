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
        if (Loader::includeModule($this->MODULE_ID))
        {
            NewSmile\VisitTable::getEntity()->createDbTable();
            NewSmile\ClinicTable::getEntity()->createDbTable();
            NewSmile\DoctorTable::getEntity()->createDbTable();
            NewSmile\PatientCardTable::getEntity()->createDbTable();
            NewSmile\ScheduleTable::getEntity()->createDbTable();
            NewSmile\ScheduleTemplateTable::getEntity()->createDbTable();
            NewSmile\WorkChairTable::getEntity()->createDbTable();
            NewSmile\WaitingListTable::getEntity()->createDbTable();

            $this->testInstallDB();
        }
    }

    public function uninstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID))
        {
            $connection = Application::getInstance()->getConnection();
            $connection->dropTable(NewSmile\VisitTable::getTableName());
            $connection->dropTable(NewSmile\ClinicTable::getTableName());
            $connection->dropTable(NewSmile\DoctorTable::getTableName());
            $connection->dropTable(NewSmile\PatientCardTable::getTableName());
            $connection->dropTable(NewSmile\ScheduleTable::getTableName());
            $connection->dropTable(NewSmile\ScheduleTemplateTable::getTableName());
            $connection->dropTable(NewSmile\WorkChairTable::getTableName());
            $connection->dropTable(NewSmile\WaitingListTable::getTableName());
        }
    }
    public function testInstallDB()
    {
        for ($i = 1; $i < 4; $i++) {
            NewSmile\WorkChairTable::add(array(
                "NAME" => 'Chair ' . $i
            ));
            NewSmile\DoctorTable::add(array(
                "NAME" => 'Doctor ' . $i
            ));
            NewSmile\PatientCardTable::add(array(
                "NAME" => 'Patient ' . $i
            ));
        }
    }
}
