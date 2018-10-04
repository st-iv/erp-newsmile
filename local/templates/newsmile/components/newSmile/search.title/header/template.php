<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Mmit\NewSmile;
use Bitrix\Main\Localization\Loc;

?>

<div class="search_content js-header-search-content">
    <div class="search_fake_header">
        <div class="search_adv_button">Расширенный поиск</div>
    </div>

    <?NewSmile\Ajax::start('header-search', true, false);?>

    <div class="search_result" <?=NewSmile\Ajax::getAreaIdAttr()?>>

        <?foreach($arResult['CATEGORIES'] as $categoryCode => $category):?>
            <?
            $searchResClass = 'search_res_' . (($categoryCode == 'patientcard') ? 'patients' : 'doctors');
            ?>
            <div class="<?=$searchResClass?>">
                <div class="search_res_title"><?=$category['TITLE']?></div>

                <?if($categoryCode == 'patientcard'):?>
                    <div class="search_res_patients_с">

                        <?foreach ($category['SUBCATEGORIES'] as $subcategoryCode => $items):?>
                            <?$isMainSubCat = ($subcategoryCode == 'MAIN');?>

                            <div class="search_res_list">

                                <?if(!$isMainSubCat):?>
                                    <div class="search_res_subtitle">
                                        <?=Loc::getMessage('MMIT_NS_ST_HEADER_FIELD_TITLE_' . strtoupper($categoryCode) . '_' . $subcategoryCode)?><span><?=count($category['SUBCATEGORIES'][$subcategoryCode])?></span>
                                    </div>
                                <?endif;?>

                                <div class="search_res_cont">

                                    <?foreach ($items as $item):?>

                                        <div class="search_res_item">
                                            <div class="search_item_fl">
                                                <div class="search_item_name"><?= ($isMainSubCat ? $item['SEARCH_ENTRY'] : $item['FIO']) ?></div>
                                                <div class="search_item_age"><?=$item['AGE']?></div>
                                            </div>

                                            <?if(!$isMainSubCat):?>
                                                <div class="search_item_phone"><?=$item['SEARCH_ENTRY']?></div>
                                            <?endif;?>
                                        </div>

                                    <?endforeach;?>

                                </div>
                            </div>
                        <?endforeach;?>
                    </div>

                <?else:?>

                    <div class="search_res_list">
                        <div class="search_res_cont">
                            <?foreach ($category['SUBCATEGORIES']['MAIN'] as $item):?>
                                <div class="search_res_item_doct">
                                    <div class="search_item_dname" style="background-color: <?=$item['COLOR']?>;">
                                        <?=($item['SEARCH_ENTRY'] ?: $item['FIO'])?>
                                    </div>
                                    <div class="search_item_dage"><?=$item['AGE']?></div>

                                    <?if($item['ENTRIES']['PERSONAL_PHONE']):?>
                                        <div class="search_item_dphone"><?=$item['ENTRIES']['PERSONAL_PHONE']?></div>
                                    <?endif;?>
                                </div>
                            <?endforeach;?>
                        </div>
                    </div>

                <?endif;?>
            </div>
        <?endforeach;?>
    </div>

    <?
    $jsParams = array(
        'minQueryLength' => $arParams['MIN_QUERY_LENGTH']
    );
    ?>

    <?NewSmile\Ajax::finish();?>
</div>

<form class="header_search_form js-ajax-load" action="<?=POST_FORM_ACTION_URI?>" data-ajax-area="header-search" id="header-search-form">
    <input type="text" name="q" class="search_str" placeholder="Искать пациента, врача, документ" autocomplete="off" />
    <div class="search_sbmt"></div>
</form>

<script>
    var headerSearchTitle = new HeaderSearchTitle(<?=\CUtil::PhpToJSObject($jsParams)?>);
</script>