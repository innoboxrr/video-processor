<?php

use Illuminate\Support\Facades\Route;

// Upload
Route::post('initiate-upload', 'S3MultipartController@initiateUpload')
	->name('initiate.upload');

Route::post('sign-part-upload', 'S3MultipartController@signPartUpload')
	->name('sign.part.upload');

Route::post('complete-upload', 'S3MultipartController@completeUpload')
	->name('complete.upload');

// Pending. Create retry video process route

// Vtt files

// Original Vtt files

Route::get('{code}/original-vtt', 'VttController@getOriginalVtt')
	->name('get.original.vtt');

Route::post('auto-generate-original-vtt', 'VttController@autoGenerateOriginalVtt')
    ->name('auto.generate.original.vtt');

Route::post('upload-original-vtt', 'VttController@uploadOriginalVtt')
    ->name('upload.original.vtt');

Route::post('delete-original-vtt', 'VttController@deleteOriginalVtt')
    ->name('delete.original.vtt');

// Translated Vtt files

Route::get('{code}/translated-vtt/{filename}', 'VttController@getTranslatedVtt')
	->name('get.translated.vtt');

Route::post('auto-generate-translated-vtt', 'VttController@autoGenerateTranslatedVtt')
    ->name('auto.generate.translated.vtt');

Route::post('upload-translated-vtt', 'VttController@uploadTranslatedVtt')
    ->name('upload.translated.vtt');

Route::post('delete-translated-vtt', 'VttController@deleteTranslatedVtt')
    ->name('delete.translated.vtt');

// Player

// Responsable for serving the player view
Route::get('player/{code}', 'VideoController@player')
	->name('player');

Route::get('playlist/{code}/{filename}', 'VideoController@playlist')
	->name('playlist');

Route::get('secret/{code}/{key}', 'VideoController@key')
	->name('key');
