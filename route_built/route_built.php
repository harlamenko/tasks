<?php

// TODO: порефакторить

$results = [];
$write_result = require '../utils/write_result.php';

function parse_connections($file) {
    preg_match("/^(?'nodes_count'\d+)\s+(?'connections_count'\d+)$/", trim(fgets($file)), $matches);
    $connections = [];

    for ($i=0; $i < $matches['connections_count']; $i++) {
        preg_match("/^(?'from_node'\d+)\s+(?'to_node'\d+)\s+(?'time'\d+)$/", trim(fgets($file)), $connections[]);
    }

    $reversed_connections = [];

    foreach ($connections as $i => $connection) {
        $connections[$i]['used'] = false;
        $reversed_connections[] = [
            'from_node' => $connection['to_node'],
            'to_node' => $connection['from_node'],
            'time' => $connection['time'],
            'used' => false
        ];
    }
    
    return array_merge($connections, $reversed_connections);
}

function parse_requests($file) {
    preg_match("/^(?'value'\d+)$/", trim(fgets($file)), $requests_count);
    $requests = [];

    for ($i=0; $i < $requests_count['value']; $i++) {
        preg_match("/^(?'from_node'\d+)\s+(?'to_node'\d+)\s+(?'request_type'-1|\?|\d+)$/", trim(fgets($file)), $requests[]);
    }
    
    return $requests;
}

function find_connection($req, $connections, $used_id = null) {
    foreach ($connections as $id => $connection) {
        if (
            ($connection['from_node'] !== $req['from_node'] || $connection['to_node'] !== $req['to_node']) &&
            ($connection['from_node'] !== $req['to_node'] || $connection['to_node'] !== $req['from_node'])
        ) { continue; };
        if ($used_id !== null && $used_id === $id) { continue; }
        return $id;
    }

    return null;
}

function count_time($from, $to, $connections) {
    foreach ($connections as $i => $path) {
        if (
            $path['from_node'] !== $from ||
            $path['used'] ||
            $path['time'] === null
        ) {
            continue;
        } else if ($path['to_node'] === $to) {
            return $path['time'];
        } else {
            $connections[$i]['used'] = true;
            $back_connection_id = find_connection($path, $connections, $i);
            $connections[$back_connection_id]['used'] = true;
            $time = count_time($path['to_node'], $to, $connections);

            if ($time !== -1) {
                return $path['time'] + $time;
            }
        }
    }

    return -1;
}

function handle_request($req, $connections) {
    global $results;

    switch ($req['request_type']) {
        case '-1':
            $connection_id = find_connection($req, $connections);
            if ($connection_id !== null) {
                $back_connection_id = find_connection($req, $connections, $connection_id);
                $connections[$connection_id]['time'] = null;
                $connections[$back_connection_id]['time'] = null;
            }
            break;
        case '?':
            $time = count_time($req['from_node'], $req['to_node'], $connections);
            $results[] = $time;
            break;
        default:
            $connection_id = find_connection($req, $connections);
            if ($connection_id === null) {
                $back_connection_id = find_connection($req, $connections, $connection_id);
                $connections[] = [
                    'from_node' => $req['from_node'],
                    'to_node' => $req['to_node'],
                    'time' => $req['time'],
                    'used' => false
                ];
                $connections[] = [
                    'from_node' => $req['to_node'],
                    'to_node' => $req['from_node'],
                    'time' => $req['time'],
                    'used' => false
                ];
            } else {
                $connections[$connection_id]['time'] = $req['request_type'];
            }
            break;
    }
    return $connections;
}

function main() {
    global $write_result, $results;

    $file = fopen('./input.txt','r') or die("не удалось открыть файл с входными данными");
    $connections = parse_connections($file);
    $requests = parse_requests($file);

    foreach ($requests as $req) {
        $connections = handle_request($req, $connections);
    }

    fclose($file);
    $write_result(implode("\n", $results));
}

main();