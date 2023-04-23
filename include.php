<?php
use Bitrix\Main;

Main\Loader::registerAutoLoadClasses('itgaziev.pricecalc', [
    'ITGaziev\PriceCalc\PriceCalcTable' => '/lib/table.php',
    'ITGaziev\PriceCalc\Main' => '/lib/main.php'
]);