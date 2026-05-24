<?php
// Emulate CodeIgniter boot and run ActiveUsers
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

require_once SYSTEMPATH . 'Config/DotEnv.php';
(new CodeIgniter\Config\DotEnv(ROOTPATH))->load();

$app = Config\Services::codeigniter();
$app->initialize();

// Mock Auth
$userModel = new \App\Models\UserModel();
$user = $userModel->find(1);
auth()->login($user);

$c = new \App\Controllers\Admin\ActiveUsers();
$c->initController(\Config\Services::request(), \Config\Services::response(), \Config\Services::logger());
$response = $c->data();
echo $response->getBody();
