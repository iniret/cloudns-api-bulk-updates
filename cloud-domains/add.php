<?php

// This script adds cloud domains in bulk to an existing master zone

// Auth ID and Password
define("AUTH_ID", 0);
define("AUTH_PASS", '');

// Master zone to add cloud domains
$master_zone = 'example.com';

// Cloud domains to be added, one per row
$cloud_domains = 'example-1.com
example-2.com
example-3.com';

/*
 * The configuration end here
 * 
 * Do not change anything below this lines, 
 * if you do not know what you are doing
 */

// checking if we can log in successfully
$login = apiCall('dns/login.json');
if (isset($login['status']) && $login['status'] == 'Failed') {
	die($login['statusDescription']."\n");
}

// check the master zone
$zone_info = apiCall('dns/get-zone-info.json', array('domain-name'=>$master_zone));
if (isset($zone_info['status']) && $zone_info['status'] == 'Failed') {
	die("Missing primary zone {$master_zone}\n");
}
if ($zone_info['type'] != 'master') {
	die("Zone {$master_zone} is not primary zone. Please check the zone and try again.\n");
}

foreach (explode("\n", $cloud_domains) as $row) {
	$zone_name = trim($row);
	if (empty($zone_name)) {
		continue;
	}
	
	$response = apiCall('dns/add-cloud-domain.json', array('domain-name'=>$master_zone, 'cloud-domain-name'=>$zone_name));
	echo $zone_name,": ",$response['statusDescription'],"\n";
}

function apiCall ($path, $post = array()) {
	$url = "https://api.cloudns.net/{$path}";

	$post['auth-id'] = AUTH_ID;
	$post['auth-password'] = AUTH_PASS;
	
	$init = curl_init();
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_POST, true);
	curl_setopt($init, CURLOPT_POSTFIELDS, http_build_query($post));
	curl_setopt($init, CURLOPT_USERAGENT, 'cloudns_api_script/0.1 (+https://github.com/ClouDNS/cloudns-api-bulk-updates/tree/master/cloud-domains/add.php)');
	$content = curl_exec($init);
	curl_close($init);
	return json_decode($content, true);
}

