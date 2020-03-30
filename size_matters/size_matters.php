<?php
$write_result = require '../utils/write_result.php';

function fill_segment_zeros($seg) {
    return str_pad(trim($seg), 4, 0, STR_PAD_LEFT);
}

function fill_zeros($IPv6) {
    return implode(':', array_map('fill_segment_zeros', explode(':', implode(':', $IPv6))));
}

function prepare($line, $IPv6_segments_len) {
    $IPv6 = explode(':', trim($line));
    $colons = str_repeat(':', $IPv6_segments_len - count($IPv6));

    foreach($IPv6 as $i => $v) {
        if (strlen($v)) { continue; }
        
        $IPv6[$i] = $colons; break;
    }   
    
    return $IPv6;
}

function write_result($result) {
    $output = fopen('./output.txt','w');
    fwrite($output, $result);
    fclose($output);
}

function main() {
    global $write_result;

    $file = fopen('./input.txt','r') or die("не удалось открыть файл с входными данными");
    $IPv6_segments_len = 8;
    $results = [];

    while ($short_IPv6 = fgets($file)) {
        $results[] = fill_zeros(prepare($short_IPv6, $IPv6_segments_len));
    }
    
    fclose($file);
    write_result(implode("\n", $results));
}

main();