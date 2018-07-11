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
            NewSmile\StatusPatientTable::getEntity()->createDbTable();
            NewSmile\TreatmentPlanTable::getEntity()->createDbTable();
            NewSmile\TreatmentPlanItemTable::getEntity()->createDbTable();
            NewSmile\ScheduleTable::getEntity()->createDbTable();
            NewSmile\ScheduleTemplateTable::getEntity()->createDbTable();
            NewSmile\WorkChairTable::getEntity()->createDbTable();
            NewSmile\WaitingListTable::getEntity()->createDbTable();

            NewSmile\ScheduleTemplateTable::addWeekSchedule();
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
            $connection->dropTable(NewSmile\StatusPatientTable::getTableName());
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
        $arWorkChairs = array(
            'Кресло 1',
            'Кресло 2',
            'Кресло 3',
        );
        foreach ($arWorkChairs as $workChair)
        {
            NewSmile\WorkChairTable::add(array(
                "NAME" => $workChair
            ));
        }
        $arDoctors = array(
            array(
                'NAME' => 'Васильева Е.В.',
                'COLOR' => '#FF9E55'
            ),
            array(
                'NAME' => 'Виноградова И.Б.',
                'COLOR' => '#FFFD64'
            ),
            array(
                'NAME' => 'Груничев В.А.',
                'COLOR' => '#D8FF5C'
            ),
            array(
                'NAME' => 'Иванова В.В.',
                'COLOR' => '#9BFF55'
            ),
            array(
                'NAME' => 'Столяров И.П.',
                'COLOR' => '#5EFF77'
            ),
        );
        foreach ($arDoctors as $arDoctor)
        {
            NewSmile\DoctorTable::add(array(
                "NAME" => $arDoctor['NAME'],
                "COLOR" => $arDoctor['COLOR']
            ));
        }
        $arPatient = array(
            'Калинина А.И',
            'Сергеева И.Г',
            'Горохов Ф.А',
            'Акилов Г.Р',
            'Полумиленко Л.П',
            'Авганец В.В',
        );
        foreach ($arPatient as $patient)
        {
            NewSmile\PatientCardTable::add(array(
                "NAME" => $patient
            ));
        }
        $arStatusPatient = array(
            'Первичный',
            'Отконсультирован',
            'Повторный',
        );
        foreach ($arStatusPatient as $statusPatient)
        {
            NewSmile\StatusPatientTable::add(array(
                "NAME" => $statusPatient
            ));
        }
    }
}
