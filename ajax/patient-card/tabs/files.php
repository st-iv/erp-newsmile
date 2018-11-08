<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
use Mmit\NewSmile;
?>

<?NewSmile\Ajax::start('patient-files');?>

<?
$filter = [
    'PATIENT_ID' => $_REQUEST['PATIENT_ID']
];

if($_REQUEST['TYPE'])
{
    $filter['TYPE'] = explode(',', $_REQUEST['TYPE']);
}

$groupBy = $_REQUEST['FILES_GROUP_BY'] ?: 'DATE_CREATE';
$sortOrder = $_REQUEST['FILES_SORT_ORDER'] ?: 'desc';

$APPLICATION->IncludeComponent(
    "newSmile:entity.list",
    "files-list",
    Array(
        'DATA_MANAGER_CLASS_ELEMENT' => '\\Mmit\\NewSmile\\FileTable',
        'ELEMENT_QUERY_PARAMS' => [
            'select' => ['NAME', 'TYPE', 'DATE_CREATE', 'TEETH', 'FILE'],
            'order' => [
                $groupBy => $sortOrder
            ],
            'filter' => $filter
        ],
        'PREVIEW_FIELDS' => ['NAME', 'TYPE', 'DATE_CREATE', 'TEETH'],
        'ELEMENT_GROUP_BY' => $groupBy,
        'ELEMENT_SORT_BY' => $groupBy,
        'ELEMENT_SORT_ORDER' => $sortOrder,
    )
);?>

<?NewSmile\Ajax::finish();?>

<?require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_after.php");