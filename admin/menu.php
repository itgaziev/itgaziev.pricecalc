<?php

use Bitrix\Main\Localization\Loc;

$accessLevel = (string) $APPLICATION->GetGroupRight('itgaziev.pricecalc');
if($accessLevel > 'D') {
    Loc::loadMessages(__FILE__);

    $ozMenu = [
        'parent_menu' => 'global_menu_marketing',
        'section' => 'itgaziev_pricecalc',
        'sort' => 1000,
        'text' => Loc::getMessage("ITGAZIEV_MENU_MAIN"),
        'title' => Loc::getMessage("ITGAZIEV_MENU_MAIN"),
        'icon' => 'itgaziev_icon',
        'items_id' => 'itgaziev_main',
        'items' => [
            [
                'text' => Loc::getMessage("ITGAZIEV_MENU_PRICE"),
                'title' => Loc::getMessage("ITGAZIEV_MENU_PRICE"),
                'url' => 'itgaziev.pricecalc_list.php?lang='.LANGUAGE_ID,
                'more_url' => array(
                    'itgaziev.pricecalc_detail.php',
                    'itgaziev.pricecalc_process.php'
                )
            ]
        ],
    ];

    return $ozMenu;

} else {
    return false;
}