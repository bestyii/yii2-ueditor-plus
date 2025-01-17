UEditorPlus
================
基于 UEditor 二次开发的富文本编辑器，让UEditor重新焕发活力, 是一套开源的在线HTML编辑器，主要用于让用户在网站上获得所见即所得编辑效果，开发人员可以用 UEditor 把传统的多行文本输入框(textarea)替换为可视化的富文本输入框。
UEditorPlus 使用 JavaScript 编写，本扩展已完美实现与 Yii2 的兼容开发。

本组件包版本号引用UEditorPlus版本号,方便对齐.

安装:
------------
使用 [composer](http://getcomposer.org/download/) 下载:
```
# 3.x(yii >= 2.0.16):
composer require bestyii/yii2-ueditor-plus:"~3.9"

# 开发版:
composer require bestyii/yii2-ueditor-plus:"dev-master"
```


使用:
-----
在`Controller`中添加:

```php
public function actions()
{
    return [
        'UeUpload' => [
            'class' => 'bestyii\ueditor\plus\UEditorPlusUpload',
            // 可选参数, 参考 config.php
            'config' => [
                'imageMaxSize' => 1*1024*1024,
                'imageAllowFiles' => ['.png', '.jpg', '.jpeg', '.gif', '.bmp'],
                'imagePathFormat' => '/uploads/image/{yyyy}{mm}/{yy}{mm}{dd}_{hh}{ii}{ss}_{rand:4}',
                'thumbStatus' => false,
                'thumbWidth' => 300,
                'thumbHeight' => 200,
                'thumbMode' => 'outbound',
            ],
        ],
    ];
}
```

在`View`中添加:

```php
// 1. 简单调用(基于模型):
$form->field($model, 'content')->widget('bestyii\ueditor\plus\UEditorPlus');

// 2. 带参数调用(此模式下仅`$editorOptions`参数可用):
$form->field($model, 'content')->widget('bestyii\ueditor\plus\UEditorPlus',[
    'editorOptions' => [
        'initialFrameWidth' => 1000,
        'initialFrameHeight' => 500,
    ],
]);

// 3. 不带`$model`调用(已列出所有可用参数, 可适当忽略):
\bestyii\ueditor\plus\UEditorPlus::widget([
    'id' => 'editor',
    'attribute' => 'content',
    //'name' => 'content',
    'value' => '初始化编辑器时的内容',
    'editorOptions' => [
        'initialFrameWidth' => 1000,
        'initialFrameHeight' => 500,
    ],
]);
```

编辑器相关配置，请在`view`中配置，参数为`editorOptions`，比如定制菜单，编辑器大小等等，具体参数请查看[UeditorPlus官网文档](https://open-doc.modstart.com/ueditor-plus/)
