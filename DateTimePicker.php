<?php

namespace akryll\datetimepicker;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;
use Yii;
use yii\web\View;
use DateTime;

class DateTimePicker extends InputWidget
{
    public $clientOptions = [];
    public $clientEvents = [];
    
    public $options = [];
    public $language = null;

    public $format = null;
    public $innerFormat = null;

    public $selector = null;

    public function init()
    {
        parent::init();

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->getId();
        }
        if ($this->innerFormat === null) {
            $this->innerFormat = 'X';
        }
    }

    public function run()
    {
        if ($this->language === null) {
            $this->language = preg_replace('/^([^-]+)[-a-z]*$/i', '$1', Yii::$app->language);
        }
        if ($this->innerFormat === null) {
            if ($this->hasModel()) {
                echo Html::activeTextInput($this->model, $this->attribute, $this->options);
            } else {
                echo Html::textInput($this->name, $this->value, $this->options);
            }
        } else {
            echo Html::textInput(false, $this->value, array_merge($this->options, ['id' => "{$this->options['id']}-replacement"]));
            if ($this->hasModel()) {
                echo Html::activeHiddenInput($this->model, $this->attribute, $this->options);
            } else {
                echo Html::hiddenInput($this->name, $this->value, $this->options);
            }
        }
        $this->registerClientScript();
        $this->registerClientEvents();
    }

    public function registerClientScript()
    {
        $clientOptions = $this->getClientOptions();
        $options = empty($clientOptions) ? '' : Json::encode($clientOptions);
        
        if ($this->selector === null) {
            if ($this->innerFormat !== null) {
                $this->selector = "#{$this->options['id']}-replacement";
            } else {
                $this->selector = '#'.$this->options['id'];
            }

        }

        $js = "jQuery('{$this->selector}').datetimepicker({$options});";
        $js .= "jQuery('{$this->selector}').on('keydown', function (event) {
            if (event.keyCode == 13) {
                jQuery(this).data('DateTimePicker').hide();
                return false;
            }
        });";
        if ($this->innerFormat !== null) {
            $offset = (new DateTime())->getOffset();
            $jsEventFunction = 'function onDateTimePickerChange(event, pickerSelector, inputSelector, targetSelector, innerFormat) {
                var datetime = moment(jQuery(inputSelector).val(), jQuery(pickerSelector).data("DateTimePicker").format, false);
                var value = "";
                if (datetime.isValid()) {
                    value = datetime.format(innerFormat);
                }
                jQuery(targetSelector).val(value);
            }';
            $js .= "jQuery('{$this->selector}').on('dp.change, dp.hide', function (event) {
                onDateTimePickerChange(event, '{$this->selector}', '#{$this->options['id']}-replacement', '#{$this->options['id']}', '{$this->innerFormat}');
            });";
            $js .= "jQuery('#{$this->options['id']}-replacement').on('change', function (event) {
                onDateTimePickerChange(event, '{$this->selector}', '#{$this->options['id']}-replacement', '#{$this->options['id']}', '{$this->innerFormat}');
            });";

            $js .= "(function () { var value = jQuery('#{$this->options['id']}').val(), datetime = moment(value, '{$this->innerFormat}', true);
                moment().zone($offset);
                if (datetime.isValid()) {
                    jQuery('{$this->selector}').data('DateTimePicker').setDate(datetime);
                }
            })();";
        } else {
            $jsEventFunction = false;
        }
        $view = $this->getView();
        DateTimePickerAsset::register($view);
        $view->registerJs($js);
        if ($jsEventFunction) {
            $view->registerJs($jsEventFunction, View::POS_READY, 'onDateTimePickerChange');
        }
    }

    protected function registerClientEvents()
    {
        if (!empty($this->clientEvents)) {
            $id = $this->options['id'];
            $js = [];
            foreach ($this->clientEvents as $event => $handler) {
                $js[] = "jQuery('#$id').on('$event', $handler);";
            }
            $this->getView()->registerJs(implode("\n", $js));
        }
    }

    public function getClientOptions()
    {
        $options = [];
        foreach (['format', 'language'] as $option) {
            if ($this->$option !== null) {
                $options[$option] = $this->$option;
            }
        }
        return array_replace($options, $this->clientOptions);
    }

}