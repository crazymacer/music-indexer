#!/usr/bin/env php
<?php
	error_reporting(E_ALL);	
	
	function getArtistID($artist_name, $artists){
		return array_search($artist_name, $artists);
	}	
	
	require_once 'lib/getid3/getid3.php' ;
	$getID3 = new getID3;
	
	require_once 'lib/db.php';
	$db = new DB();

	$cmd = "find /home/music -name \"*.mp3\"";
	exec($cmd, $out);
	
	$count = count($out);
	echo "Total files found: {$count}\n";
	
	$artists = [];
	$tracks = [];
	
	for($i=0;$i<$count;$i++) {
		$trackInfo = $getID3->analyze($out[$i]);
		echo "Processed file: {$i}/{$count} ({$trackInfo["filenamepath"]})\n";
		if(isset($trackInfo["tags"]["id3v2"])){
			
			$track = $trackInfo["tags"]["id3v2"]["artist"][0];
			if(array_search($track, $artists) === False) {
				array_push($artists, $track);
			}
			
			$tmp = [
				'filename' => $trackInfo["filenamepath"],
				'title' => $trackInfo["tags"]["id3v2"]["title"][0],
				'artist_id' => getArtistID($track, $artists)+1,
				'album' => $trackInfo["tags"]["id3v2"]["album"][0],
				'year' => (int)$trackInfo["tags"]["id3v2"]["year"][0],
				'bitrate' => (int)$trackInfo["audio"]["bitrate"],
				'lenght' => (int)$trackInfo["playtime_seconds"]
			];
			array_push($tracks, $tmp);
		}else{
			array_push($tracks, NULL);
		}
	}
	
	for($i=0;$i<count($artists);$i++){
		$tmp = [
			'id' => NULL,
			'artist' => $artists[$i]
		];
		$db->insert('artists', $tmp);
	}
	for($i=0;$i<count($tracks);$i++) {
		$db->insert('tracks', $tracks[$i]);
	}
?>