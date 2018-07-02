<?php
/**
 * Created by PhpStorm.
 * User: niki_
 * Date: 20.06.2018
 * Time: 17:14
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
    Bitrix\Main\Type\DateTime,
    Bitrix\Main\Type\Date,
    Bitrix\Main\Config\Option,
    Mmit\NewSmile\VisitTable,
    Mmit\NewSmile\ScheduleTable,
    Mmit\NewSmile\WorkChairTable;

if (!Loader::includeModule('mmit.newSmile')) {
    echo json_encode(array(
        'result' => 'error'
    ));
    die();
}

if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action'])
    {
        case 'selectDoctor':
            if ((isset($_REQUEST['schedule_id']) || isset($_REQUEST['new_schedule'])) && isset($_REQUEST['doctor_id'])) {
                foreach ($_REQUEST['schedule_id'] as $idSchedule)
                {
                    ScheduleTable::update(
                        intval($idSchedule),
                        array(
                            'DOCTOR_ID' => intval($_REQUEST['doctor_id'])
                        )
                    );
                }
                foreach ($_REQUEST['new_schedule'] as $arSchedule)
                {
                    ScheduleTable::add(array(
                        'TIME' => new DateTime(date('d.m.Y H:i', strtotime($arSchedule['TIME']))),
                        'CLINIC_ID' => 1,
                        'DOCTOR_ID' => intval($_REQUEST['doctor_id']),
                        'WORK_CHAIR_ID' => $arSchedule['WORK_CHAIR'],
                    ));
                }
                echo json_encode(array(
                    'result' => 'success'
                ));
            }
            break;
        case 'selectDoctorDay':
            if (isset($_REQUEST['time'])
                && isset($_REQUEST['doctor_id'])
                && isset($_REQUEST['work_chair'])) {

                $isResult = ScheduleTable::appointDoctorHalfDay(strtotime($_REQUEST['time']), $_REQUEST['doctor_id'], $_REQUEST['work_chair']);

                if ($isResult) {
                    echo json_encode(array(
                        'result' => 'success'
                    ));
                }
            }
            break;
        case 'addVisit':
            $arFiled = array();
            if (!empty($_REQUEST['TIME_START'])) {
                $timeStart = new DateTime($_REQUEST['TIME_START'], 'Y-m-d H:i');
                $arFiled['TIME_START'] = $timeStart;

                $date = new Date(date('Y-m-d',strtotime($_REQUEST['TIME_START'])), 'Y-m-d');
                $arFiled['DATE_START'] = $date;
            }
            if (!empty($_REQUEST['TIME_END'])) {
                $timeEnd = new DateTime(date('Y-m-d H:i',strtotime($_REQUEST['TIME_END']) + ScheduleTable::TIME_15_MINUTES), 'Y-m-d H:i');
                $arFiled['TIME_END'] = $timeEnd;
            }
            if (!empty($_REQUEST['PATIENT_ID'])) {
                $arFiled['PATIENT_ID'] = $_REQUEST['PATIENT_ID'];
            }
            if (!empty($_REQUEST['DOCTOR_ID'])) {
                $arFiled['DOCTOR_ID'] = $_REQUEST['DOCTOR_ID'];
            }
            if (!empty($_REQUEST['WORK_CHAIR_ID'])) {
                $arFiled['WORK_CHAIR_ID'] = $_REQUEST['WORK_CHAIR_ID'];
            }

            if (!empty($arFiled)) {
                try {
                    $rsSchedule = ScheduleTable::getList(array(
                        'filter' => array(
                            '>=TIME' => $arFiled['TIME_START'],
                            '<TIME' => $arFiled['TIME_END'],
                            'WORK_CHAIR_ID' => $arFiled['WORK_CHAIR_ID'],
                            'ENGAGED' => 'N'
                        )
                    ));
                    if ($rsSchedule->getSelectedRowsCount() > 0) {
                        VisitTable::add($arFiled);
                        while ($arSchedule = $rsSchedule->fetch())
                        {
                            ScheduleTable::update($arSchedule['ID'], array(
                                'ENGAGED' => 'Y'
                            ));
                        }
                    }else {
                        echo json_encode(array(
                            'result' => 'error',
                            'error' => 'Данное время занято или не рабочее'
                        ));
                    }
                } catch (Exception $e) {
                    echo json_encode(array(
                        'result' => 'error',
                        'error' => $e->getMessage()
                    ));
                }
            }
            break;
    }
}