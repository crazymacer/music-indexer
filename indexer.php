#!/usr/bin/env php
<?php
	error_reporting(0);	
	
	require_once "config.php";
	require_once "lib/getid3/getid3.php";
	require_once "lib/db.php";
	
	$getID3 = new getID3;
	$db = new DB();

	function getArtistID($artist_name, $artists){
		return array_search($artist_name, $artists)+1;
	}
	function getAlbumID($album_name, $albums){
		return array_search($album_name, $albums);
	}
	
	$db->query_unsafe("SET foreign_key_checks = 0;
		TRUNCATE TABLE `artists`;
		TRUNCATE TABLE `albums`;
		TRUNCATE TABLE `tracks`;");

	$cmd = "find {$conf['music']['dir']} -name \"*.mp3\"";
	exec($cmd, $out);
	
	$count = count($out);
	echo "Total files found: {$count}\n";
	
	$tracks = [];
	$artists = [];
	$albums = [];
	
	for($i=0;$i<$count;$i++) {
		$trackInfo = $getID3->analyze($out[$i]);
		if(isset($trackInfo["tags"]["id3v2"])){
			$artist = $trackInfo["tags"]["id3v2"]["artist"][0];
			$album = $trackInfo["tags"]["id3v2"]["album"][0];			
			
			if(array_search($artist, $artists) == false){
				array_push($artists, $artist);
			}
			if(array_search($album, $albums) == false){
				array_push($albums, $album);
			}
			
			$file = explode("/", $trackInfo["filenamepath"]);
			for($j=0;$j<(count($file)-1);$j++){
				array_shift($file);
			}
			$file = implode("/", $file);
			
			$tmp = [
				'filename' => $file,
				'title' => $trackInfo["tags"]["id3v2"]["title"][0],
				'artist_id' => getArtistID($artist, $artists),
				'album_id' => getAlbumID($album, $albums),
				'year' => (int)$trackInfo["tags"]["id3v2"]["year"][0],
				'bitrate' => (int)$trackInfo["audio"]["bitrate"],
				'lenght' => (int)$trackInfo["playtime_seconds"]
			];
			array_push($tracks, $tmp);
		}else{
			array_push($tracks, NULL);
		}
		echo "Processed file: {$i}/{$count} ({$file})\n";
	}
	
	foreach($artists as $artist){
		$tmp = [
			'id' => NULL,
			'artist' => $artist
		];
		$db->insert('artists', $tmp);
	}
	foreach($albums as $album){
		$tmp = [
			'id' => NULL,
			'album' => $album
		];
		$db->insert('albums', $tmp);
	}
	foreach($tracks as $track){
		$db->insert('tracks', $track);
	}
?>