<?php


namespace api\modules\v1\forms;


use common\components\InputModelBehavior;
use common\models\Post;
use common\models\PostCategory;
use oks\filemanager\models\Files;
use yii\base\Model;
use yii\web\UnauthorizedHttpException;
use yii\web\UploadedFile;

class BlogForm extends Model
{

	public $slug;
    public $title;
    public $intro;
    public $content;
    public $image;
    public $published_on;
    public $lang;
    public $category_id;
    public function behaviors()
    {
        return [
            'file_manager_model' => [
                'class' => InputModelBehavior::class,
                'delimitr' => ','
            ],
        ];
    }

    public function rules()
    {
        return[
        [['title','content', 'lang'], 'required'],
        [['image','lang','title','content','intro','category_id'],'string'],
        [['lang','published_on'],'integer']
        ];
    }

    public function save(){
    	if ($this->slug) {
    		$blog = Post::findOne(['slug' => $this->slug]);
    		if ($blog->status == Post::STATUS_ACTIVE)
				throw new UnauthorizedHttpException("Blog post already published");
		} else $blog = new Post();

        $keys = array_keys($_FILES);
        $response = [];
        foreach ($keys as $key) {
            $files = UploadedFile::getInstancesByName($key);
            if (count($files)) {
                foreach ($files as $file):
                    $model = new Files();
                    $model->file_data = $file;
                    if ($model->save()) {
                        $blog->image .= $model->file_id . ",";
                    }
                    if ($model->hasErrors()):
                        $response['errors'] = $model->getErrors();
                        return $response;
                    endif;
                endforeach;
            }
        }
        $blog->type = Post::TYPE_BLOG;
        $blog->title = $this->title;
        $blog->content = $this->content;
        $blog->intro = $this->intro;
        $blog->lang = $this->lang;
        $blog->published_on = time();
        $blog->user_id = \Yii::$app->user->identity->id;
        $blog->status = Post::STATUS_WAITING;
        if ($blog->save()) {
            if(is_array($this->category_id)){
                foreach ($this->category_id as $item) {
                    $category = new PostCategory();
                    $category->post_id = $blog->id;
                    $category->category_id = $item;
                    $category->save();

                }
            }
            \Yii::$app->response->setStatusCode(201);
            return $blog;
        } else {
            \Yii::$app->response->setStatusCode(422);
            return $blog->getErrors();
        }
    }

}