<?php
use app\assets\AppAsset;

/* @var $this \yii\web\View */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" ng-app="app">
<head>
<meta charset="<?= Yii::$app->charset ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Angular Yii Application</title>
<?php $this->head() ?>

<script>paceOptions = {ajax: {trackMethods: ['GET', 'POST']}};</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/red/pace-theme-minimal.css" rel="stylesheet" />
</head>
<body ng-controller="MainController">
<?php $this->beginBody() ?>
<input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" />

    <md-toolbar scroll-header class="md-toolbar-menu" layout="row" layout-align="center" md-colors="{background: 'white'}">
        <div flex class="layout-max-width" layout="row">
            <div class="md-toolbar-tools">
                <md-button class="md-icon-button md-primary" ng-click="toggleLeft()" hide-gt-sm aria-label="Menu">
                    <md-icon md-svg-src="/img/icons/menu.svg"></md-icon>
                </md-button>
                <a href="/" ng-show="!showSearch">My Company</a>
                <span flex ng-show="!showSearch"></span>
                <md-input-container flex ng-show="showSearch">
                    <label>&nbsp;</label>
                    <input ng-model="search.who" placeholder="Search">
                </md-input-container>
                <div ng-show="!showSearch" show-gt-sm hide>
                    <md-button ui-sref="home">Home</md-button>
                    <md-button ui-sref="adminService">Services</md-button>
                    <md-button ui-sref="adminBlog" ng-show="loggedIn()">Blog</md-button>
                    <md-button ui-sref="adminTag" ng-show="loggedIn()">Tag</md-button>
                    <md-button ui-sref="adminEmployer" ng-show="loggedIn()">Employer</md-button>
                    <md-button ui-sref="adminTour" ng-show="loggedIn()">Tour</md-button>
                    <md-button ui-sref="register" ng-hide="loggedIn()">Register</md-button>
                </div>
                <md-button class="md-button-search" aria-label="Search" ng-click="showSearch = !showSearch">
                    <i class="fa fa-search"></i>
                </md-button>
                <div class="md-button-login">
                    <md-button class="md-fab" ng-click="toggleRight()" ng-hide="loggedIn()"><i class="fa fa-user"></i> Login</md-button>
                    <md-button ng-click="toggleRight()" class="md-fab" ng-click="logout()" ng-show="loggedIn()"><i class="fa fa-user"></i> Profile</md-button>
                </div>
            </div>
        </div>
    </md-toolbar>

    <md-sidenav layout="column" class="md-sidenav-left md-whiteframe-z2" md-component-id="left">
      <md-toolbar class="md-tall" md-colors="{background: 'emerald-green'}">
        <span flex></span>
        <div layout="column" class="md-toolbar-tools-bottom inset">
          <user-avatar></user-avatar>
          <span></span>
          <div>Firstname Lastname</div>
          <div>email@domainname.com</div>
          <md-button href="/logout" class="md-fab" ng-click="logout()"><i class="fa fa-user"></i> Logout</md-button>
      </div>
    </md-toolbar>
    <md-list>
      <md-list-item>
        <a href="#">
          <md-item-content md-ink-ripple layout="row" layout-align="start center">
            <div class="inset">
              <i class="fa fa-home"></i>
          </div>
          <div class="inset">Home
          </div>
      </md-item-content>
    </a>
    </md-list-item>
    <md-list-item>
        <a href="#">
          <md-item-content md-ink-ripple layout="row" layout-align="start center">
            <div class="inset">
              <i class="fa fa-shield"></i>
          </div>
          <div class="inset">About
          </div>
      </md-item-content>
    </a>
    </md-list-item>
    <md-list-item>
        <a href="#">
          <md-item-content md-ink-ripple layout="row" layout-align="start center">
            <div class="inset">
              <i class="fa fa-comment"></i>
          </div>
          <div class="inset">Contact
          </div>
      </md-item-content>
    </a>
    </md-list-item>
    <md-divider></md-divider>
    <md-subheader>Management</md-subheader>
    <md-item ng-repeat="item in admin">
        <a>
          <md-item-content md-ink-ripple layout="row" layout-align="start center">
            <div class="inset">
              <ng-md-icon icon="{{item.icon}}"></ng-md-icon>
          </div>
          <div class="inset">{{item.title}}
          </div>
      </md-item-content>
    </a>
    </md-item>
    </md-list>
    </md-sidenav>

    <md-sidenav class="md-sidenav-right md-whiteframe-4dp jumbotron" md-component-id="right">
      <md-tabs md-dynamic-height class="md-accent" ng-hide="loggedIn()">
        <md-tab label="Signin">
          <md-content ng-controller="LoginController" ng-include="'/partials/login.html'" layout="column" layout-padding>
        </md-content>
        </md-tab>
        <md-tab label="Register">
          <md-content ng-controller="RegisterController" ng-include="'/partials/register.html'" layout="column" layout-padding>
        </md-content>
        </md-tab>
      </md-tabs>
        
      <div ng-controller="ProfileController" ng-include="'/partials/profile.html'" ng-show="loggedIn()"></div>
    </md-sidenav>

    <ui-view></ui-view>

    <footer class="footer">
        <div class="container">
            <p class="pull-left">&copy; <a href="http://blog.neattutorials.com">Neat Tutorials</a> <?= date('Y') ?></p>
            <p class="pull-right"><?= Yii::powered() ?> <?= Yii::getVersion() ?></p>
        </div>
    </footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>