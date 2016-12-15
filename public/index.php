<?php
    require_once '../autoloader.php';

    $app = CodeJetter\core\App::getInstance();
    $app->init();

if (\CodeJetter\core\Registry::getConfigClass()->get('debug') === true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(-1);
}

//    $result = (new \CodeJetter\components\user\mappers\MemberUserMapper())->updateById(1, [
//        'name' => 'ehsanNew'
//    ]);

    /*
     * how to use validator
     */
//    $dummyInput = [
//        'childId' => '0',
//        'parentId' => 1,
////        'URL'   => 'http://'
//    ];
//
//    $rule = new \CodeJetter\core\security\ValidatorRule('required');
//    $rule2 = new \CodeJetter\core\security\ValidatorRule('email');
//    $rule3 = new \CodeJetter\core\security\ValidatorRule('size', ['size' => 3]);
//    $rule4 = new \CodeJetter\core\security\ValidatorRule('URL');
//    $rule5 = new \CodeJetter\core\security\ValidatorRule('id');
//
////    $input = new \CodeJetter\core\io\Input('name');
////    $input->addRule($rule);
////    $input->addRule($rule3);
////
////    $emailInput = new \CodeJetter\core\io\Input('email');
////    $emailInput->addRule($rule);
////    $emailInput->addRule($rule2);
////    $emailInput->addRule($rule3);
////
////    $urlInput = new \CodeJetter\core\io\Input('URL');
////    $urlInput->addRule($rule4);
//
//    $childId = new \CodeJetter\core\io\Input('childId');
//    $childId->setRules([
//        $rule,
//        $rule5
//    ]);
//    $parentId = new \CodeJetter\core\io\Input('parentId');
//    $parentId->setRules([
//        $rule,
//        $rule5
//    ]);
//
//    $inputs = [
//        $childId,
//        $parentId
//    ];
//
//    $validator = new \CodeJetter\core\security\Validator($inputs, $dummyInput);
//    $validator->validate();
//
//    var_dump($validator->getErrors());exit;

    //var_dump($_SESSION);exit;
    //var_dump((new \CodeJetter\components\user\services\UserAuthentication())->getLoggedIn());exit;

    \CodeJetter\core\Registry::getRouterClass()->route();

//    $language = \CodeJetter\core\Registry::getLanguageClass();
//
//    var_dump($language->get('hello'));
//
//    $language->setCurrentLanguage('fa');
//
//    var_dump($language->getAll());
//    var_dump($language->get('bye'));
//
//    $language->setCurrentLanguage('en');
//
//    var_dump($language->get('bye'));
//    exit;
