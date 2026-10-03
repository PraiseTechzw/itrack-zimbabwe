<?php
require_once dirname(__DIR__) . '/core/Controller.php'; require_once dirname(__DIR__) . '/models/PurchaseOrder.php'; require_once dirname(__DIR__) . '/models/Supplier.php';
class PurchasesController extends Controller {
 private PurchaseOrder $model; private Supplier $suppliers;
 public function __construct(){ $this->model=new PurchaseOrder(); $this->suppliers=new Supplier(); }
 public function index(): void { $this->requireModuleAccess('purchases', ['Administrator','Procurement Officer','Store Officer']); $this->view('purchases/index',['title'=>'Purchases','orders'=>$this->model->all(),'suppliers'=>$this->suppliers->all(),'error'=>$_SESSION['flash_error']??null]); unset($_SESSION['flash_error']); }
 public function create(): void { $this->requireLogin(); if($_SERVER['REQUEST_METHOD']==='POST'){ if(!$this->validateCsrf()){$_SESSION['flash_error']='Invalid security token';$this->redirect('/index.php?controller=purchases');} $this->model->create($_POST+['ordered_by'=>$_SESSION['user']['id']??null]); } $this->redirect('/index.php?controller=purchases'); }
 public function status(): void { $this->requireLogin(); if($this->validateCsrf() || $_SERVER['REQUEST_METHOD']!=='POST') $this->model->updateStatus($this->sanitizeInt($_GET['id']??$_POST['id']??0),$this->sanitize($_POST['status']??$_GET['status']??'pending')); $this->redirect('/index.php?controller=purchases'); }
}
