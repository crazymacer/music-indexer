#!/usr/bin/env php
<?php
	error_reporting(E_ALL);

	require_once('lib/getid3/getid3.php');
	$getID3 = new getID3;

	$cmd = "find /home/music/College/ -name \"*.mp3\"";
	exec($cmd, $out);
	
	$count = count($out);
	echo "Total files found: {$count}\n";
	
	$songs = [];
	for($i=0;$i<$count;$i++) {
		$trackInfo = $getID3->analyze($out[$i]);
		
		echo "Processing file: {$i}/{$count} ({$trackInfo["filenamepath"]})\n";
		
		if(isset($trackInfo["tags"]["id3v2"])){
			$tmp = [
				'filename' => $trackInfo["filenamepath"],
				'title' => $trackInfo["tags"]["id3v2"]["title"][0],
				'artist' => $trackInfo["tags"]["id3v2"]["artist"][0],
				'album' => $trackInfo["tags"]["id3v2"]["album"][0],
				'bitrate' => $trackInfo["audio"]["bitrate"],
				'lenght' => $trackInfo["playtime_seconds"]
			];
			array_push($songs, $tmp);
		}else{
			array_push($songs, NULL);
		}
	}
	print_r($songs);
?>