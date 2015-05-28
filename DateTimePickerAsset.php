<?php

namespace akryll\datetimepicker;

use yii\web\AssetBundle;

class DateTimePickerAsset extends AssetBundle
{
    public $sourcePath = '@bower/eonasdan-bootstrap-datetimepicker/build/';
    public $js = [
        'js/bootstrap-datetimepicker.min.js',
    ];
    public $css = [
        'css/bootstrap-datetimepicker.min.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'akryll\datetimepicker\MomentAsset',
    ];
}
