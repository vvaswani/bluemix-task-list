<?php
session_start();
require_once 'vendor/Slim/Slim.php';
require_once 'vendor/google-api-php-client/src/Google_Client.php';
require_once 'vendor/google-api-php-client/src/contrib/Google_TasksService.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->config(array(
  'debug' => true,
  'templates.path' => './templates'
));
$client = new Google_Client();
$client->setClientId('YOUR-CLIENT-ID');
$client->setClientSecret('YOUR-CLIENT-SECRET');
$client->setRedirectUri('http://YOUR-HOST/login');
$client->setScopes(array(
  'https://www.googleapis.com/auth/tasks'
));
$app->client = $client;
$app->tasksService = new Google_TasksService($app->client);

$app->get('/', function () use ($app) {
  $app->redirect('/index');
});

$app->get('/login', function () use ($app) {
  
    if (isset($_GET['code'])) {
      $app->client->authenticate();
      $_SESSION['access_token'] = $app->client->getAccessToken();
      $app->redirect('/index');
      exit;
    }  

    // if token available in session, set token in client
    if (isset($_SESSION['access_token'])) {
      $app->client->setAccessToken($_SESSION['access_token']);
    }

    if ($app->client->getAccessToken()) {
      if (isset($_SESSION['target'])) {
        $app->redirect($_SESSION['target']);
      } else {
        $app->redirect('/index');
      }
    } else {
      $authUrl = $app->client->createAuthUrl();
      $app->redirect($authUrl);
    }
  
});

$app->get('/index', 'authenticate', function () use ($app) {
  $lists = $app->tasksService->tasklists->listTasklists();
  foreach ($lists['items'] as $list) {
    $id = $list['id'];
    $tasks[$id] = $app->tasksService->tasks->listTasks($id);
  }
  $app->render('index.php', array('lists' => $lists, 'tasks' => $tasks));
});

$app->get('/add-list', 'authenticate', function () use ($app) {
  $app->render('add-list.php');    
});

$app->post('/add-list', 'authenticate', function () use ($app) {
  if (isset($_POST['submit'])) {
    $title = trim(htmlentities($_POST['title']));
    if (empty($title)) {
      $title = 'Untitled List';
    }
    $tasklist = new Google_TaskList();
    $tasklist->setTitle($title);
    $result = $app->tasksService->tasklists->insert($tasklist);
    $app->redirect('/index');
  } 
});

$app->get('/delete-list/:lid', 'authenticate', function ($lid) use ($app) {
  $app->tasksService->tasklists->delete($lid);
  $app->redirect('/index');
});

$app->get('/add-task/:tid', 'authenticate', function ($tid) use ($app) {
  $app->render('add-task.php', array('id' => $tid));    
});

$app->post('/add-task', 'authenticate', function () use ($app) {
  if (isset($_POST['submit'])) {
    $title = trim(htmlentities($_POST['title']));
    $due = trim(htmlentities($_POST['due']));
    $id = trim(htmlentities($_POST['id']));
    if (empty($title)) {
      $title = 'Untitled Task';
    }
    if (empty($due)) {
      $due = 'tomorrow';
    }
    $task = new Google_Task();
    $task->setTitle($title);
    $task->setDue(date(DATE_RFC3339, strtotime($due)));
    $result = $app->tasksService->tasks->insert($id, $task);
    $app->redirect('/index');
  } 
});

$app->get('/delete-task/:lid/:tid', 'authenticate', function ($lid, $tid) use ($app) {
  $app->tasksService->tasks->delete($lid, $tid);
  $app->redirect('/index');
});

$app->get('/update-task/:lid/:tid', 'authenticate', function ($lid, $tid) use ($app) {
    $task = new Google_Task($app->tasksService->tasks->get($lid, $tid));
    $task->setStatus('completed');
    $result = $app->tasksService->tasks->update($lid, $task->getId(), $task);
    $app->redirect('/index');
});

$app->get('/logout', function () use ($app) {
  unset($_SESSION['access_token']);    
  $app->client->revokeToken();
});

$app->run();

function authenticate () {
  $app = \Slim\Slim::getInstance();
  $_SESSION['target'] = $app->request()->getPathInfo();
  if (isset($_SESSION['access_token'])) {
    $app->client->setAccessToken($_SESSION['access_token']);
  }
  if (!$app->client->getAccessToken()) {
    $app->redirect('/login');
  }
}  
