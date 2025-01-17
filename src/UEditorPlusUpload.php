<?php
namespace bestyii\ueditor\plus;

use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use moxuandi\helpers\Helper;
use moxuandi\helpers\Uploader;

/**
 * UEditor 接收上传图片控制器.
 *
 */
class UEditorPlusUpload extends Action
{
    /**
     * @var array 上传配置接口
     */
    public $config = [];


    public function init()
    {
        parent::init();
        Yii::$app->request->enableCsrfValidation = false;  // 关闭csrf
        $_config = require(__DIR__ . '/config.php');  // 加载默认上传配置
        $this->config = array_merge($_config, $this->config);
    }

    public function run()
    {
        switch(Yii::$app->request->get('action')){
            // 上传图片 & 多图上传
            case 'uploadimage':
                // 上传涂鸦
            case 'uploadscrawl':
                // 上传视频
            case 'uploadvideo':
                // 上传附件
            case 'uploadfile': $result = self::actionUpload(); break;

            // 图片在线管理(列出图片)
            case 'listimage':
                // 在线附件(列出文件)
            case 'listfile': $result = self::actionList(); break;

            // 配置参数
            case 'config': $result = Json::encode($this->config); break;
            // 抓取远程文件
            //case 'catchimage': $result = []; break;
            default: $result = Json::encode(['state'=>'请求地址出错']); break;
        }

        // 输出结果
        if($callback = Yii::$app->request->get('callback')){
            if(preg_match("/^[\w_]+$/", $callback)){
                echo htmlspecialchars($callback) . '(' . $result . ')';
            }else{
                echo Json::encode(['state'=>'callback 参数不合法']);
            }
        }else{
            echo $result;
        }
        exit();
    }

    /**
     * 处理上传
     */
    private function actionUpload()
    {
        $base64 = 'upload';
        switch(Yii::$app->request->get('action')){
            // 上传图片 & 多图上传
            case 'uploadimage':
                $config = [
                    'pathFormat' => $this->config['imagePathFormat'],
                    'maxSize' => $this->config['imageMaxSize'],
                    'allowFiles' => $this->config['imageAllowFiles'],
                    'thumbStatus' => $this->config['thumbStatus'],
                    'thumbWidth' => $this->config['thumbWidth'],
                    'thumbHeight' => $this->config['thumbHeight'],
                    'thumbMode' => $this->config['thumbMode'],
                ];
                $fieldName = $this->config['imageFieldName'];
                break;
            // 上传涂鸦
            case 'uploadscrawl':
                $config = [
                    'pathFormat' => $this->config['scrawlPathFormat'],
                    'maxSize' => $this->config['scrawlMaxSize'],
                    'realName' => 'scrawl.png'
                ];
                $fieldName = $this->config['scrawlFieldName'];
                $base64 = 'base64';
                break;
            // 上传视频
            case 'uploadvideo':
                $config = [
                    'pathFormat' => $this->config['videoPathFormat'],
                    'maxSize' => $this->config['videoMaxSize'],
                    'allowFiles' => $this->config['videoAllowFiles']
                ];
                $fieldName = $this->config['videoFieldName'];
                break;
            // 上传附件
            case 'uploadfile':
            default:
                $config = [
                    'pathFormat' => $this->config['filePathFormat'],
                    'maxSize' => $this->config['fileMaxSize'],
                    'allowFiles' => $this->config['fileAllowFiles']
                ];
                $fieldName = $this->config['fileFieldName'];
                break;
        }

        $config['rootPath'] = ArrayHelper::getValue($this->config, 'rootPath', dirname(Yii::$app->request->scriptFile));

        // 生成上传实例对象并完成上传, 返回结果数据
        $upload = new Uploader($fieldName, $config, $base64);
        return Json::encode([
            'original' => $upload->realName,  // 原始文件名, eg: 'img_6.jpg'
            'name' => $upload->fileName,       // 新文件名, eg: '171210_054500_8166.jpg'
            'title' => $upload->fileName,      // 新文件名, eg: '171210_054500_8166.jpg'
            'url' => $upload->fullName,  // 返回的地址, eg: '/uploads/image/201712/171210_054500_8166.jpg'
            'size' => $upload->fileSize,       // 文件大小, eg: 108527
            'type' => '.' . $upload->fileExt,  // 文件类型, eg: '.jpg'
            'state' => $upload->stateInfo,     // 上传状态, 上传成功时必须返回'SUCCESS'
        ]);
    }

    /**
     * 列出已上传的文件列表
     */
    private function actionList()
    {
        switch(Yii::$app->request->get('action')){
            // 在线附件(列出文件)
            case 'listfile':
                $allowFiles = $this->config['fileManagerAllowFiles'];
                $listSize = $this->config['fileManagerListSize'];
                $path = $this->config['fileManagerListPath'];
                break;
            // 图片在线管理(列出图片)
            case 'listimage':
            default:
                $allowFiles = $this->config['imageManagerAllowFiles'];
                $listSize = $this->config['imageManagerListSize'];
                $path = $this->config['imageManagerListPath'];
                break;
        }

        // 允许列出的文件类型, eg: 'png|jpg|jpeg|gif|bmp'
        $allowFiles = substr(str_replace('.', '|', implode('', $allowFiles)), 1);

        // 获取参数
        $getStart = Yii::$app->request->get('start');
        $getSize = Yii::$app->request->get('size');
        $start = isset($getStart) ? htmlspecialchars($getStart) : 0;
        $size = isset($getSize) ? htmlspecialchars($getSize) : $listSize;
        $end = (int)$start + (int)$size;

        // 获取文件列表
        $rootPath = Helper::trimPath(dirname(Yii::getAlias('@app')), '/') . '/web';
        $path = $rootPath . $path;
        $files = self::getFiles($path, $rootPath, $allowFiles);
        $lenth = count($files);  // 文件总数
        if($lenth === 0){  // 如果没有文件
            return Json::encode(['state' => '没有匹配的文件', 'list' => [], 'start' => $start, 'total' => $lenth]);  // 'state' => 'no match file'
        }

        // 获取指定范围的文件列表
        for($i = min($end, $lenth) - 1, $list = []; $i < $lenth && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }

        // 获取指定范围的文件列表(倒序)
        /*for($i = $end, $list = []; $i < $lenth && $i < $end; $i++){
            $list[] = $files[$i];
        }*/

        // 返回数据
        return Json::encode(['state' => 'SUCCESS', 'list' => $list, 'start' => $start, 'total' => $lenth]);
    }

    protected function getFiles($path, $rootPath, $allowFiles, &$files=[])
    {
        if(!is_dir($path)) return null;  // 不是目录, 返回 null

        // 最后一个字符必须是'/'
        if(substr($path, strlen($path) - 1) != '/'){
            $path .= '/';
        }

        if($handle = opendir($path)){  // 打开目录句柄
            while(false !== ($file = readdir($handle))){  // 循环获取文件, readdir()每次返回一个文件
                if(!in_array($file, ['.', '..'])){
                    $path2 = $path . $file;
                    if(is_dir($path2)){
                        self::getFiles($path2, $rootPath, $allowFiles, $files);
                    }else{
                        if(preg_match('/\.(' . $allowFiles . ')$/i', $file)){
                            $files[] = [
                                'url' => substr($path2, strlen($rootPath)),
                                'mtime' => filemtime($path2)
                            ];
                        }
                    }
                }
            }
            closedir($handle);  // 关闭目录句柄
        }
        return $files;
    }
}
