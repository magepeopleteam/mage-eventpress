<?php
$_POST['option_sale_start_date'] = ["2026-05-20", "2026-05-21"];
$_POST['option_sale_start_time'] = ["14:30", "00:00"];

function mage_array_strip($data) {
    if (is_array($data)) {
        foreach ($data as $k => $v) $data[$k] = trim($v);
        return $data;
    }
    return trim($data);
}

$data = [
    ['option_name' => 'Ticket 1'],
    ['option_name' => 'Ticket 2']
];

$sale_start_date = $_POST['option_sale_start_date'] ? mage_array_strip($_POST['option_sale_start_date']) : array();
$sale_start_time = $_POST['option_sale_start_time'] ? mage_array_strip($_POST['option_sale_start_time']) : array();
if (sizeof($sale_start_date) > 0) {
    $count = count($data);
    for ($i = 0; $i < $count; $i++) {
        if (is_array($data) && array_key_exists( $i, $data )) {
            $data[$i]['option_sale_start_date'] = !empty($sale_start_date[$i]) ? stripslashes(strip_tags($sale_start_date[$i])) : '';
            $data[$i]['option_sale_start_time'] = !empty($sale_start_time[$i]) ? stripslashes(strip_tags($sale_start_time[$i])) : '';
            $data[$i]['option_sale_start_date_t'] = !empty($sale_start_date[$i]) ? stripslashes(strip_tags($sale_start_date[$i] . ' ' . $sale_start_time[$i])) : '';
        }
    }
}
var_dump($data);
