<?php
use app\assets\AdminAsset;

/* @var $this \yii\web\View */

AdminAsset::register($this);
dmstr\web\AdminLteAsset::register($this);
$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
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
<body class="hold-transition skin-blue sidebar-mini" ng-controller="MainController">
<?php $this->beginBody() ?>
<input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" />

<div class="wrapper">

    <?= $this->render(
        'header.php',
        ['directoryAsset' => $directoryAsset]
    ) ?>

    <?= $this->render(
        'left.php',
        ['directoryAsset' => $directoryAsset]
    )
    ?>

    <?= $this->render(
        'content.php',
        ['content' => $content, 'directoryAsset' => $directoryAsset]
    ) ?>

</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>