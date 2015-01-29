<?php

$list_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getlistsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);

//people connection or list present?
$cash_admin->page_data['connection'] = AdminHelper::getConnectionsByScope('users') || $list_response['payload'];

// Return Connection
$page_data_object = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
$settings_types_data = $page_data_object->getConnectionTypes('users');

$all_services = array();
$typecount = 1;
foreach ($settings_types_data as $key => $data) {
	if ($typecount % 2 == 0) {
		$alternating_type = true;
	} else {
		$alternating_type = false;
	}
	if (file_exists(ADMIN_BASE_PATH.'/assets/images/settings/' . $key . '.png')) {
		$service_has_image = true;
	} else {
		$service_has_image = false;
	}
	if (in_array($cash_admin->platform_type, $data['compatibility'])) {
		$all_services[] = array(
			'key' => $key,
			'name' => $data['name'],
			'description' => $data['description'],
			'link' => $data['link'],
			'alternating_type' => $alternating_type,
			'service_has_image' => $service_has_image
		);
		$typecount++;
	}
}
$cash_admin->page_data['all_services'] = new ArrayIterator($all_services);


// lists
if (is_array($list_response['payload'])) {
	$cash_admin->page_data['lists_all'] = new ArrayIterator($list_response['payload']);
}

$user_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getuser',
		'user_id' => $cash_admin->effective_user_id
	)
);
if (is_array($user_response['payload'])) {
	$current_userdata = $user_response['payload']['data'];
}

$session_news = AdminHelper::getActivity($current_userdata);
if ($session_news) {
	// now set up page variables
	$total_new = false;
	if (is_array($session_news['activity']['lists'])) {
		$total_new = false;
		foreach ($session_news['activity']['lists'] as &$list_stats) {
			$total_new = $total_new + $list_stats['total'];
			if ($list_stats['total'] == 1) {
				$list_stats['singular'] = true;
			} else {
				$list_stats['singular'] = false;
			}
		}
		if ($total_new == 1) {
			$cash_admin->page_data['people_singular'] = true;
		} else {
			$cash_admin->page_data['people_singular'] = false;
		}
	}
	$cash_admin->page_data['dashboard_list_total_new'] = $total_new;
	$cash_admin->page_data['dashboard_lists'] = $session_news['activity']['lists'];	
}


$cash_admin->page_data['current_date'] = date('m/d/Y');
$cash_admin->setPageContentTemplate('people');
?>