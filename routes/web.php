<?php

use Illuminate\Support\Facades\Route;

// Upload
Route::post('initiate-upload', 'S3MultipartController@initiateUpload')
	->name('initiate.upload');

Route::post('sign-part-upload', 'S3MultipartController@signPartUpload')
	->name('sign.part.upload');

Route::post('complete-upload', 'S3MultipartController@completeUpload')
	->name('complete.upload');


// Player

// Responsable for serving the player view
Route::get('player/{code}', 'VideoController@player')
	->name('player');

Route::get('playlist/{code}/{filename}', 'VideoController@playlist')
	->name('playlist');

Route::get('secret/{code}/{key}', 'VideoController@key')
	->name('key');
