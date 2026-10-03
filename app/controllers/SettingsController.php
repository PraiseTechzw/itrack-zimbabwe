<?php
require_once dirname(__DIR__) . '/core/Controller.php';
class SettingsController extends Controller { public function index():void{$this->requireModuleAccess('settings', ['Administrator','Director']);$this->view('settings/index',['title'=>'Settings','config'=>require dirname(__DIR__).'/config/app.php']);} }
