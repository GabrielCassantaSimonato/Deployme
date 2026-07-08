<?php
namespace app; //classe responsável por ter as rotas do sistema

use MF\init\Bootstrap;

class route extends Bootstrap
{ //classe route que herda a classe bootstrap
    protected function initRoutes()
    { //preparação das rotas para ir ao controller
        $routes['home'] = array('route' => '/', 'controller' => 'IndexController', 'action' => 'index');
        $routes['signUpStudent'] = array('route' => '/signUpStudent', 'controller' => 'IndexController', 'action' => 'signUpStudent');
        $routes['signUpRecruiter'] = array('route' => '/signUpRecruiter', 'controller' => 'IndexController', 'action' => 'signUpRecruiter');
        $routes['lgpd'] = array('route' => '/lgpd', 'controller' => 'IndexController', 'action' => 'lgpd');
        $routes['login'] = array('route' => '/login', 'controller' => 'IndexController', 'action' => 'login');
        $routes['studentRegister'] = array('route' => '/studentRegister', 'controller' => 'IndexController', 'action' => 'studentRegister');
        $routes['recruiterRegister'] = array('route' => '/recruiterRegister', 'controller' => 'IndexController', 'action' => 'recruiterRegister');
        $routes['timeline'] = array('route' => '/timeline', 'controller' => 'AppController', 'action' => 'timeline');
        $routes['successRegister'] = array('route' => '/successRegister', 'controller' => 'IndexController', 'action' => 'successRegister');
        $routes['resumeAnalyzer'] = array('route' => '/resumeAnalyzer', 'controller' => 'IAController', 'action' => 'resumeAnalyzer');
        $routes['auth'] = array('route' => '/auth', 'controller' => 'AuthController', 'action' => 'auth');
        $routes['logout'] = array('route' => '/logout', 'controller' => 'AuthController', 'action' => 'logout');
        $routes['loginAdmin'] = array('route' => '/loginAdmin', 'controller' => 'IndexController', 'action' => 'loginAdmin');
        $routes['admin'] = array('route' => '/admin', 'controller' => 'AdminController', 'action' => 'admin');
        $routes['profile'] = array('route' => '/profile', 'controller' => 'ProfileController', 'action' => 'profile');
        $routes['editProfile'] = array('route' => '/editProfile', 'controller' => 'ProfileController', 'action' => 'editProfile');
        $routes['updateProfile'] = array('route' => '/updateProfile', 'controller' => 'ProfileController', 'action' => 'updateProfile');
        $routes['post'] = array('route' => '/post', 'controller' => 'AppController', 'action' => 'post');
        $routes['vacancy'] = array('route' => '/vacancy', 'controller' => 'AppController', 'action' => 'vacancy');
        $routes['updatePost'] = array('route' => '/updatePost', 'controller' => 'AppController', 'action' => 'updatePost');
        $routes['updateVacancy'] = array('route' => '/updateVacancy', 'controller' => 'AppController', 'action' => 'updateVacancy');
        $routes['deletePost'] = array('route' => '/deletePost', 'controller' => 'AppController', 'action' => 'deletePost');
        $routes['deleteVacancy'] = array('route' => '/deleteVacancy', 'controller' => 'AppController', 'action' => 'deleteVacancy');
        $routes['like'] = array('route' => '/like', 'controller' => 'AppController', 'action' => 'like');
        $routes['unlike'] = array('route' => '/unlike', 'controller' => 'AppController', 'action' => 'unlike');
        $routes['comment'] = array('route' => '/comment', 'controller' => 'AppController', 'action' => 'comment');
        $routes['deleteComment'] = array('route' => '/deleteComment', 'controller' => 'AppController', 'action' => 'deleteComment');
        $routes['editComment'] = array('route' => '/editComment', 'controller' => 'AppController', 'action' => 'editComment');
        $routes['share'] = array('route' => '/share', 'controller' => 'AppController', 'action' => 'share');
        $routes['forgotPassword'] = array('route' => '/forgotPassword', 'controller' => 'IndexController', 'action' => 'forgotPassword');
        $routes['resetPassword'] = array('route' => '/resetPassword', 'controller' => 'IndexController', 'action' => 'resetPassword');
        $routes['people'] = array('route' => '/people', 'controller' => 'AppController', 'action' => 'people');
        $routes['follow'] = array('route' => '/follow', 'controller' => 'AppController', 'action' => 'follow');
        $routes['unFollow'] = array('route' => '/unFollow', 'controller' => 'AppController', 'action' => 'unFollow');
        $routes['followers'] = ['route' => '/followers', 'controller' => 'AppController', 'action' => 'followers'];
        $routes['viewVacancies'] = ['route' => '/viewVacancies', 'controller' => 'AppController', 'action' => 'viewVacancies'];
        $routes['vacancyDetails'] = ['route' => '/vacancyDetails', 'controller' => 'appController', 'action' => 'vacancyDetails'];
        $routes['applyVacancy'] = array('route' => '/applyVacancy', 'controller' => 'AppController', 'action' => 'applyVacancy');
        $routes['myApplications'] = array('route' => '/myApplications', 'controller' => 'AppController', 'action' => 'myApplications');
        $routes['myVacancies'] = array('route' => '/myVacancies', 'controller' => 'AppController', 'action' => 'myVacancies');
        $routes['vacancyCandidates'] = array('route' => '/vacancyCandidates', 'controller' => 'AppController', 'action' => 'vacancyCandidates');
        $routes['updateApplicationStatus'] = array('route' => '/updateApplicationStatus', 'controller' => 'AppController', 'action' => 'updateApplicationStatus');
        $routes['withdrawApplication'] = array('route' => '/withdrawApplication', 'controller' => 'AppController', 'action' => 'withdrawApplication');
        $routes['chat'] = array('route' => '/chat', 'controller' => 'AppController', 'action' => 'chat');
        $routes['openConversation'] = array('route' => '/openConversation', 'controller' => 'AppController', 'action' => 'openConversation');
        $routes['sendMessage'] = array('route' => '/sendMessage', 'controller' => 'AppController', 'action' => 'sendMessage');
        $routes['loadMessages'] = array('route' => '/loadMessages', 'controller' => 'AppController', 'action' => 'loadMessages');
        $routes['loadConversations'] = array('route' => '/loadConversations', 'controller' => 'AppController', 'action' => 'loadConversations');
        $routes['deleteMessage'] = array('route' => '/deleteMessage', 'controller' => 'AppController', 'action' => 'deleteMessage');
        $routes['editMessage'] = array('route' => '/editMessage', 'controller' => 'AppController', 'action' => 'editMessage');
        $routes['notifications'] = array('route' => '/notifications', 'controller' => 'AppController', 'action' => 'notifications');
        $routes['loadNotifications'] = array('route' => '/loadNotifications', 'controller' => 'AppController', 'action' => 'loadNotifications');
        $routes['readNotification'] = array('route' => '/readNotification', 'controller' => 'AppController', 'action' => 'readNotification');
        $routes['deleteNotification'] = array('route' => '/deleteNotification', 'controller' => 'AppController', 'action' => 'deleteNotification');
        $routes['countNotifications'] = array('route' => '/countNotifications', 'controller' => 'AppController', 'action' => 'countNotifications');
        $routes['deactivateAccount'] = array('route' => '/deactivateAccount', 'controller' => 'ProfileController', 'action' => 'deactivateAccount');
        $routes['reactivateAccount'] = array('route' => '/reactivateAccount', 'controller' => 'AuthController', 'action' => 'reactivateAccount');
        $routes['reactivateAccountAction'] = array('route' => '/reactivateAccountAction', 'controller' => 'AuthController', 'action' => 'reactivateAccountAction');
        $routes['timelineAdmin'] = array('route' => '/timelineAdmin', 'controller' => 'AdminController', 'action' => 'timelineAdmin');
        $routes['deletePostAdmin'] = array('route' => '/deletePostAdmin', 'controller' => 'AdminController', 'action' => 'deletePostAdmin');
        $routes['blockUser'] = array('route' => '/blockUser', 'controller' => 'AdminController', 'action' => 'blockUser');
        $this->setRoutes($routes);//seta a rota no objeto
    }
}
?>