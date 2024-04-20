<?php

function getCpuCores() {
    if (PHP_OS_FAMILY == 'Windows') {
        $cores = shell_exec('echo %NUMBER_OF_PROCESSORS%');
    } else {
        $cores = shell_exec('nproc');
    }

    return (int) $cores;
}

function getFreeMemory() {
    if (PHP_OS_FAMILY == 'Windows' && preg_match('~(\d+)~', shell_exec('wmic OS get FreePhysicalMemory'), $matches)) {
        $freeMemory = round((int) $matches[1] / 1024);
    } else {
        if (preg_match('~MemFree:\s+(\d+)\s+~', shell_exec('cat /proc/meminfo'), $matches)) {
            $freeMemory = $matches[1] / 1024;
        }
    }
    return (int) $freeMemory;
}

function getWorkerMemory() {
    if (PHP_OS_FAMILY !== 'Windows' && preg_match_all('~(\d+).*php-fpm: pool~', shell_exec('ps -eo size,command'), $matches, PREG_PATTERN_ORDER)) {
        $processMemory = round(array_sum($matches[1]) / count($matches[1]) / 1024);
    }
    
    if (!isset($processMemory)) {
       $processMemory = round(ini_parse_quantity(ini_get('memory_limit')) / 1048576);
    }

    return (int) $processMemory;
}

$cpuCores = getCpuCores();
$freeMemory = getFreeMemory();
$workerMemory = getWorkerMemory();

// reserve 10% for system use
$memoryReserve = round(0.1 * $freeMemory);

$maxChildren = floor(($freeMemory - $memoryReserve) / $workerMemory);

echo "pm.max_children = " . $maxChildren . "\n";
echo "start_servers = " . min(round(0.25 * $maxChildren), $cpuCores * 4) . "\n";
echo "min_spare_servers = " . min(round(0.25 * $maxChildren), $cpuCores * 2) . "\n";
echo "max_spare_servers = " . min(round(0.75 * $maxChildren), $cpuCores * 4) . "\n";
