<?php
namespace bestyii\ueditor\plus;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\InputWidget;

/**
 * UEditor renders editor js plugin for classic editing.
 *
 * @see https://open-doc.modstart.com/ueditor-plus
 */
class UEditorPlus extends InputWidget
{
    /**
     * @var array 配置接口, 参阅 UEditor Plus 官方文档(https://open-doc.modstart.com/ueditor-plus)
     * @see assets/ueditor.config.js
     */
    public $editorOptions = [];


    public function init()
    {
        if($this->hasModel()){
            parent::init();
            $this->id = Html::getInputId($this->model, $this->attribute);
        }elseif($this->attribute){
            $this->id .= '_' . $this->attribute;
        }

        $_options = [
            'serverUrl' => Url::to(['UeUpload']),
            'initialFrameWidth' => 1000,
            'initialFrameHeight' => 320,
            'lang' => strtolower(Yii::$app->language) == 'en-us' ? 'en' : 'zh-cn'
        ];
        $this->editorOptions = array_merge($_options, $this->editorOptions);
    }

    public function run()
    {
        self::registerEditorScript();
        if($this->hasModel()){
            return Html::activeTextarea($this->model, $this->attribute, ['id'=>$this->id]);
        }else{
            return Html::textarea(is_null($this->name) ? $this->id : $this->name, $this->value, ['id'=>$this->id]);
        }
    }

    /**
     * 注册客户端脚本
     */
    protected function registerEditorScript()
    {
        UEditorPlusAsset::register($this->view);
        $editorOptions = Json::encode($this->editorOptions);
        $script = "UE.getEditor('{$this->id}', $editorOptions);";
        $this->view->registerJs($script);
    }
}
