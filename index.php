<?php

$language_map = array(
  'ca' => 'Uniu-vos',
  'de' => 'Mitmachen',
  'en' => 'Join',
  'es' => 'Unirse',
  'fr' => 'Join',
  'it' => 'Unisciti',
);
$default_lang = 'en';

$http_host = filter_input(INPUT_SERVER, 'HTTP_HOST');
$uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
$is_https = filter_input(INPUT_SERVER, 'HTTPS') == 'on' || filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_PROTO') == 'https';
$remote_ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
$accept_languages = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE');

$domain = '.wikitolearn.org';

if (substr($http_host, 0, 5) === 'join.') {
    $domain = substr($http_host, 4);
}

while (strpos($uri, '//') !== false) {
    $uri = str_replace('//', '/', $uri);
}

if (!$is_https) {
    header('Location: https://'.$http_host.$uri);
} else {
    $lang_requested = null;
    $whatINeed = explode('/', $uri);
    if (count($whatINeed) > 1) {
        if (strlen($whatINeed[1]) > 0) {
            $lang_requested = strtolower($whatINeed[1]);
            if (!isset($language_map[$lang_requested])) {
                $lang_requested = null;
            }
        }
    }

    if (is_null($lang_requested)) {
        if (!is_null($accept_languages)) {
            $max = 0.0;
            $lang_requested = null;
            $langs = explode(',', $accept_languages);
            foreach ($langs as $lang_item) {
                $lang = explode(';', $lang_item);
                $q = (isset($lang[1])) ? ((float) $lang[1]) : 1.0;
                if ($q > $max) {
                    $max = $q;
                    $lang_requested = $lang[0];
                }
            }
            $lang_requested = trim($lang_requested);
        } else {
            if (function_exists('geoip_record_by_name')) {
                $geo_details = geoip_record_by_name($remote_ip);
                $lang_requested = strtolower($geo_details['country_code']);
            }
        }
    }

    if (!isset($language_map[$lang_requested])) {
        $lang_requested = $default_lang;
    }
    header('Location: https://'.$lang_requested.$domain.'/'.$language_map[$lang_requested]);
}
