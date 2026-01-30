<?php

use App\Services\CacheService;

if (!function_exists('settings')) {
	function settings($key, $local = false, $defaultReturn = '') {
		$settings             =   (new CacheService())->Settings();
		if ($local){
			return array_key_exists($key . '_' . lang(), $settings) ? $settings[$key . '_' . lang()] : '';
		}
		return array_key_exists($key, $settings) ? $settings[$key] : $defaultReturn;
	}
}

function lang(){
    return App() -> getLocale();
}


