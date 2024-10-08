<?php

use Sotbit\Custom\Api\TestDataBuilder;

//массив настроек для продукта
$product_settings_array = [
    'reserve' => 100,                             // колличество товара на каждый склад
    'price' => 100,                               // цена в руб
    'max_reserve' => 100                          // по сколько продуктов идёт каждому партнёру
];

$test_builder = new TestDataBuilder(
    10,                                 //количество пользователей
    10,                                 //количество складов
    10,                             //количество поставщиков раздела магазин->поставщики
    10,                        //количество подрядчиков highload блока
    "SotbitMarketplacePartner",          // наименование highload блока
    10,                               // колличество свойств инфоблока
    7,                                   // id инфоблока
    10,                                 // колличество продуктов в инфобоке
    $product_settings_array                         //массив настроек для продукта
);

if (!$test_builder->testUsersDataBuilder()) {
    $error_msg = "Can't create test data !";
};

if (!$test_builder->testStoreDataBuilder()) {
    $error_msg = "Can't create test data !";
};

if (!$test_builder->testContractorDataBuilder()) {
    $error_msg = "Can't create test data !";
};

if (!$test_builder->testHLblockDataBuilder()) {
    $error_msg = "Can't create test data !";
};

if (!$test_builder->testInfoBlockDataBuilder()) {
    $error_msg = "Can't create test data !";
};

if (!$test_builder->testInfoBlockProductBuilder()) {
    $error_msg = "Can't create test data !";
};
