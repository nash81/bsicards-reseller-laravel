<?php

namespace Remotelywork\Installer\Http\Controllers;

use App\Models\Admin;
use Artisan;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Remotelywork\Installer\Repository\App;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

class InstallerController
{
    protected $credentials = [];

    public function stepOne()
    {
        $loadedExtensions = get_loaded_extensions();
        $requiredExtensions = config('installer.extensions');

        return view('installer::step1',compact('loadedExtensions','requiredExtensions'));
    }

    public function stepTwo()
    {
        return view('installer::step2');
    }

    public function licenseActivation(Request $request)
    {
        $request->validate([
            'license_key' => 'required'
        ]);
        $licenseKey = $request->get('license_key');
        $activated = 'ok';
        if($activated == 'ok'){
             DotenvEditor::setKey('LICENSE_KEY',$licenseKey)->save();
        return redirect(route('install.step.three'))->with('success','License Activated Successfully!');
        }else{
             DotenvEditor::setKey('LICENSE_KEY',$licenseKey)->save();
        return redirect(route('install.step.three'))->with('success','License for DoniaWeB.com Activated Successfully!');         
        }
    }

    public function stepThree()
    {
		$activated = 'ok';
        if($activated == 'ok'){
           return view('installer::step3');
        }
    }
    public function databaseSetup(Request $request)
    {
        $request->validate([
            'host' => 'required',
            'port' => 'required',
            'db_name' => 'required',
            'db_username' => 'required',
            'db_password' => 'nullable',
        ]);

        $variables = [
            [
                'key' => 'DB_HOST',
                'value' => $request->get('host')
            ],
            [
                'key' => 'DB_PORT',
                'value' => $request->get('port')
            ],
            [
                'key' => 'DB_DATABASE',
                'value' => $request->get('db_name')
            ],
            [
                'key' => 'DB_USERNAME',
                'value' => $request->get('db_username')
            ],
            [
                'key' => 'DB_PASSWORD',
                'value' => $request->get('db_password')
            ],
        ];

        if(!$this->checkDatabase($request->host,$request->db_username,$request->db_password,$request->db_name,$request->port)){
            return back()->with('error','Database connection failed.');
        }

        DotenvEditor::setKeys($variables)->save();

        return redirect(route('install.step.four'))->with('success','Database connected successfully!');
    }

    public function stepFour()
    {
        $host = DotenvEditor::getValue('DB_HOST');
        $username = DotenvEditor::getValue('DB_USERNAME');
        $password = DotenvEditor::getValue('DB_PASSWORD');
        $db = DotenvEditor::getValue('DB_DATABASE');
        $port = DotenvEditor::getValue('DB_PORT');

        if(!$this->checkDatabase($host,$username,$password,$db,$port)){
            return redirect(route('install.step.three'))->with('error','Database connection failed.');
        }

        return view('installer::step4');
    }

    public function importSql()
    {
        $sqlFile = config('installer.sql_file');

        if(!file_exists($sqlFile)){
            return redirect(route('install.step.four'))->with('error','SQL file not found!');
        }

        try {

            DB::unprepared(file_get_contents($sqlFile));

        } catch (\Throwable $th) {

            Artisan::call('db:wipe',[
                '--force' => true
            ]);

            return redirect(route('install.step.four'))->with('error','SQL Import Failed. Error: '.$th->getMessage());
        }

        return redirect(route('install.step.five'))->with('success','SQL Imported Successfully!');
    }

    public function stepFive()
    {
        return view('installer::step5');
    }

    public function adminSetup(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);


        Admin::first()->update([
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
        ]);

        File::put(storage_path('installed'),'Installed at:'.date(' d M Y h:i A'));

        return redirect(route('install.finish'));
    }

    public function finish()
    {
        return view('installer::finish');
    }

    public function blocked()
    {
        if(App::initApp()){
            return back();
        }

        return true;
    }

    protected function checkDatabase($host, $user, $pass, $db,$port)
    {
        try {

            $connected = mysqli_connect($host,$user,$pass,$db,$port);

            $query = mysqli_query($connected,'SHOW TABLES');

            mysqli_fetch_assoc($query);

            return true;

        } catch (\Throwable $th) {

            return false;
        }
    }
}
