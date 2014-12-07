<?php
// Data setup
$array = array();

// Change Unix timestamp to Human timestamp
function sec2human($time) {
  $seconds = $time%60;
	$mins = floor($time/60)%60;
	$hours = floor($time/60/60)%24;
	$days = floor($time/60/60/24);
	return $days > 0 ? $days . ' day'.($days > 1 ? 's' : '') : $hours.':'.$mins.':'.$seconds;
}

// Get number of CPU cores in system
function num_cpus(){
	$numCpus = 1;
	
	if(is_file('/proc/cpuinfo')){
		$cpuinfo = file_get_contents('/proc/cpuinfo');
		preg_match_all('/^processor/m', $cpuinfo, $matches);
		$numCpus = count($matches[0]);
	}elseif('WIN' == strtoupper(substr(PHP_OS, 0, 3))){
		$process = @popen('wmic cpu get NumberOfCores', 'rb');
		
		if(false !== $process){
			fgets($process);
			$numCpus = intval(fgets($process));
			
			pclose($process);
		}
	}else{
		$process = @popen('sysctl -a', 'rb');
		
		if(false !== $process){
			$output = stream_get_contents($process);
			
			preg_match('/hw.ncpu: (\d+)/', $output, $matches);
			if($matches){
				$numCpus = intval($matches[1][0]);
			}
			
		pclose($process);
		}
	}
	return $numCpus;
}

// System Uptime
$fh = fopen('/proc/uptime', 'r');
$uptime = fgets($fh);
fclose($fh);
$uptime = explode('.', $uptime, 2);
$array['uptime'] = sec2human($uptime[0]);

// Memory (RAM)
$fh = fopen('/proc/meminfo', 'r');
  $mem = 0;
  while ($line = fgets($fh)) {
    $pieces = array();
    if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces)) {
      $memoryT = $pieces[1];
    }
    if (preg_match('/^MemFree:\s+(\d+)\skB$/', $line, $pieces)) {
      $memoryF = $pieces[1];
    }
    if (preg_match('/^Cached:\s+(\d+)\skB$/', $line, $pieces)) {
      $memoryC = $pieces[1];
      break;
    }
  }
fclose($fh);
$memoryM = $memoryC + $memoryF;
$memoryM2 = $memoryM / $memoryT * 100;
$memory = round($memoryM2) . '%';
if ($memory >= "51%") { $memoryL = "success"; }
elseif ($memory <= "50%") { $memoryL = "warning"; }
elseif ($memory <= "35%") { $memoryL = "danger"; }
$array['memory'] = '<div class="progress progress-striped active">
<div class="progress-bar progress-bar-'.$memoryL.'" role="progressbar" style="width: '.$memory.';">'.$memory.'</div>
</div>';

// Storage (Disk)
$storageT = disk_total_space("/");
$storageF = disk_free_space("/");
$storageM = $storageF / $storageT * 100;
$storage = round($storageM) . '%';
if ($storage >= "51%") { $storageL = "success"; }
elseif ($storage <= "50%") { $storageL = "warning"; }
elseif ($storage <= "35%") { $storageL = "danger"; }
$array['hdd'] = '<div class="progress progress-striped active">
<div class="progress-bar progress-bar-'.$storageL.'" role="progressbar" style="width: '.$storage.';">'.$storage.'</div>
</div>';

// CPU Load
$cpuUsage = "";
exec('ps -aux', $processes);
foreach($processes as $process)
{
	$cols = split(' ', ereg_replace(' +', ' ', $process));
	if (strpos($cols[2], '.') > -1)
	{
		$cpuUsage += floatval($cols[2]);
	}
}
$cpuUsage = $cpuUsage / num_cpus().PHP_EOL;
$cpuUsage = number_format((float)$cpuUsage, 2, '.', '');
$cpuUsage = $cpuUsage.'%';
$array['load'] = $cpuUsage;

// If the server is online
$array['online'] = '<div class="progress">
<div class="progress-bar progress-bar-success" role="progressbar" style="width: 100%;"><small>Up</small></div>
</div>';

echo json_encode($array);
