<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<style>
    .document-body {
        height: 500px;
        width: 1000px;
        margin: 0 auto;
        position: relative;
    }

    .treatment-plan {
        list-style-type: none;
        width: 100%;
        padding: 0;
        border: 1.5px solid black;
    }

    .treatment-plan li {
        border-bottom: 1.5px solid black;
        padding: 5px;
    }

    .treatment-plan li:last-child {
        border-bottom: none;
    }

    .treatment-plan li.title {
        font-weight: bold;
        font-size: 20px;
    }

    .treatment-plan li.service {
        padding-left: 40px;
    }

    .patient-fio {
        font-style: italic;
        text-align: right;
        text-decoration: underline;
    }

    .sign {
        bottom: 20px;
        position: absolute;
        right: 20px;
    }
</style>

<div class="document-body">
    <div class="patient-fio">
        №<?=$data['ID']?> <?=$data['PATIENT_LAST_NAME']?> <?=$data['PATIENT_NAME']?> <?=$data['PATIENT_SECOND_NAME']?>
    </div>

    <ul class="treatment-plan">
        <li class="title">План лечения</li>
        <li>
            Итого: <?=$data['MIN_SUM']?>&#8381; - <?=$data['MAX_SUM']?>&#8381;
        </li>

        <?foreach ($data['GROUPS'] as $group):?>
            <li><?=$group['NAME']?>: <?=$group['MIN_SUM']?>&#8381; - <?=$group['MAX_SUM']?>&#8381;</li>

            <?foreach ($group['ITEMS'] as $item):?>
                <li class="service"><?=$item['TARGET']?> (<?=$item['NAME']?>): <?=$item['MIN_PRICE']?>&#8381; - <?=$item['MAX_PRICE']?>&#8381;</li>
            <?endforeach;?>
        <?endforeach;?>
    </ul>

    <div class="sign">
        Врач: _______________________________/ _________________________________/
    </div>
</div>

