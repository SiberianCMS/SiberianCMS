<?php

$this->query("
    CREATE TABLE `api_provider` (
        `provider_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `code` varchar(30) NOT NULL,
        `name` varchar(60) NOT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`provider_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `api_key` (
        `key_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `provider_id` int(11) unsigned NOT NULL,
        `key` varchar(30) NULL DEFAULT NULL,
        `value` varchar(255) NULL DEFAULT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`key_id`),
        KEY `KEY_PROVIDER_ID` (`provider_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    ALTER TABLE `api_key`
        ADD FOREIGN KEY `FK_PROVIDER_ID` (`provider_id`) REFERENCES `api_provider` (`provider_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$apis = array(
    'instagram' => array(
        'token',
        'client_id'
    ),
    'facebook' => array(
        'app_id',
        'secret_key'
    ),
    'youtube' => array(
        'api_key'
    )
);

foreach($apis as $provider_code => $keys) {
    $provider_name = ucfirst($provider_code);
    $provider = new Api_Model_Provider();
    $provider->setData(array(
        'code' => $provider_code,
        'name' => $provider_name
    ))->save();
    foreach($keys as $key) {
        $datas = array(
            'provider_id' => $provider->getId(),
            'key' => $key
        );
        $key = new Api_Model_Key();
        $key->setData($datas)->save();
    }

}