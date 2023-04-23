<?php
use ITGaziev\PriceCalc;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Main\CIBlock;
use Bitrix\Highloadblock as HL; 
use Bitrix\Main\Entity;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

Main\Loader::includeModule('itgaziev.pricecalc');

Loc::loadMessages(__FILE__);

if ($ID > 0) {
    $result = PriceCalc\PriceCalcTable::getById($ID);
    $condition = $result->fetch();
    if ($condition['PARAMETERS']) 
        $condition['PARAMETERS'] = unserialize($condition['PARAMETERS']);
} else {
    LocalRedirect("/bitrix/admin/itgaziev.pricecalc_list.php?lang=" . LANG);
}

$arJsConfig = [
    'itgaziev.pricecalc' => [
        'js' => '/local/modules/itgaziev.pricecalc/install/themes/.default/itgaziev.pricecalc/script.js',
        'css' => '/local/modules/itgaziev.pricecalc/install/themes/.default/itgaziev.pricecalc/style.css',
        'rel' => []
    ]
];

foreach ($arJsConfig as $ext => $arExt) CJSCore::RegisterExt($ext, $arExt);

CJSCore::Init(array('jquery'));
if($arJsConfig) {
    CUtil::InitJSCore(array_keys($arJsConfig));
}

$POST_RIGHT = $APPLICATION->GetGroupRight('itgaziev.pricecalc');

if($POST_RIGHT == 'D') $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));

$aTabs = [[
    'DIV' => 'edit0',
    'TAB' => Loc::getMessage('ITGAZIEV_PRICECALC_TAB'),
    'ICON' => 'main_user_edit',
    'TITLE' => Loc::getMessage('ITGAZIEV_PRICECALC_TAB_TITLE')
]];

$tabControl = new CAdminTabControl('tabControl', $aTabs, false);
$APPLICATION->SetTitle(Loc::getMessage('ITGAZIEV_PRICECALC_TAB_TITLE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$aMenu = [[
    'TEXT' => Loc::getMessage('ITGAZIEV_PRICECALC_BACK'),
    'TITLE' => Loc::getMessage('ITGAZIEV_PRICECALC_BACK_TITLE'),
    'LINK' => 'itgaziev.pricecalc_list.php?lang='.LANG,
    'ICON' => 'btn_list'
]];
?>
<form method="post" action="<?= $APPLICATION->GetCurPage() ?>?lang=<?=LANG?>" enctype="multipart/form-data" name="post_form">
<?php
echo bitrix_sessid_post();
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<tr>
    <td>Кол. товаров за шаг</td>
    <td><input type="number" name="step_count" value="800" /></td>
</tr>
<tr>
    <td>Процесс</td>
    <td>
        <div class="myProgress">
            <div class="myBar"></div>
        </div>
    </td>
</tr>
<?
//TODO : html info price
$tabControl->Buttons();
echo '<input type="submit" name="cancel" value="Вернуться" onclick="top.window.location=\'itgaziev.pricecalc_list.php?lang='. LANG . '\'" title="' . Loc::getMessage('ITGAZIEV_PRICECALC_CANCEL') . '">';
echo '<input type="button" name="export" value="Выполнить" title="Выполнить" class="adm-btn-save btn-export">';

$tabControl->End();
ob_start();
?>
</form>
<script>
    window.onload = () => { 
        const update_price = new ITGazievUpdatePrice({});
        $(document).on('click', '.btn-export', function() {
            update_price.ajaxUpdate({
                action : 'update',
                id : <?= $ID ?>,
                step_count : $('input[name="step_count"]').val(),
                page : 0,
            })
        })
    }
</script>
<?

$jsString = ob_get_clean();
Asset::getInstance()->addString($jsString);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';