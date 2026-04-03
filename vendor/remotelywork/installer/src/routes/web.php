<?php

use Remotelywork\Installer\Http\Controllers\InstallerController;


Route::middleware('web')->group(function(){
    
    Route::prefix('install')->middleware('is_installed')->name('install.')->group(function(){
        Route::redirect('/','install/step1');
        Route::get('step1',[InstallerController::class,'stepOne'])->name('step.one');
        Route::get('step2',[InstallerController::class,'stepTwo'])->name('step.two');
        Route::post('license-activation',[InstallerController::class,'licenseActivation'])->name('license.activation');
        Route::get('step3',[InstallerController::class,'stepThree'])->name('step.three');
        Route::post('database-setup',[InstallerController::class,'databaseSetup'])->name('database.setup');
        Route::get('step4',[InstallerController::class,'stepFour'])->name('step.four');
        Route::post('import-sql',[InstallerController::class,'importSQL'])->name('import.sql');
        Route::get('step5',[InstallerController::class,'stepFive'])->name('step.five');
        Route::post('admin-setup',[InstallerController::class,'adminSetup'])->name('admin.setup');
        Route::get('finish',[InstallerController::class,'finish'])->name('finish')->withoutMiddleware('is_installed');
    });

    Route::get('blocked',[InstallerController::class,'blocked']);
});
