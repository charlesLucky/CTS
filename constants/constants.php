<?php

$tester = 'hello this is to show I am included';

$users = array(
    'initials' => array('MZ', 'BVP', 'EKC', 'KS', 'HH', 'AR', 'JM', 'SR', 'SC', 'SS', 'AJ'),
);

$add_product_consts = array(
    'name' => array('HPCA', 'HPCM', 'HPCC'),
    'type' => array('AUTO', 'ALLO'),
    'ABO' => array('A', 'B', 'AB', 'O'),
    'Rh' => array('POS', 'NEG'),
    'receipt_status' => array('20-24', '1-10 transport', '-150 cryopreserved'),
    'collection_site' => array('BWH', 'BCH', 'NMDP', 'MGH', 'other'),
);

$add_patient_consts = array(
    'ABO' => array('A', 'B', 'AB', 'O'),
    'Rh' => array('POS', 'NEG'),
);

$add_process_consts = array(
    'timepoint' => array('Initial', 'Prefreeze', 'Init-Preinfusion', 'Preinfusion'),
    'process_name' => array('Freeze', 'Thaw', 'Unmanipulated', 'Plasma Deplete', 'RBC Deplete'),
    'bsc_sn' => array('79038', '79041', '79049', '79054', '79047', '79063'),
    'scale_sn' => array('29119809', '29208507', '29207713'),
    'tube_welder_sn' => array('03030031', '03030030', '11060020'),
    'centrifuge' => array('1', '2', '3', '4', '5', '6'),
    'refrigerator' => array('1', '2', '3', '4', '5', '6'),
    'kryo_10' => array('D', 'E', 'A'),
    'component_codes' => array('B0', 'C0', 'D0', 'E0', 'F0', 'G0'),
    'sterility' => array('NG', 'G', 'P'),
    'sign_offs' => array(),
);

$ln2_consts = array(
    'tank' => array('14', '18', '20', '21', '22'),
    'rack_letter' => array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'),
    'slot' => array('1', '2', '3', '4'),
);

$dtz = new DateTimeZone('America/New_York');

$dt_format_read = 'm-d-Y H:i:s';
$dt_format_write = 'Y-m-d H:i:s';
