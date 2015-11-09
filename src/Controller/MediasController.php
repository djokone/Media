<?php
namespace Media\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Utility\Inflector;
use Cake\Collection\Collection;
use Cake\Network\Exception\ForbiddenException;
//use Cake\Core\Exception\Exception;

/**
 * Medias Controller
 *
 * @property \Media\Model\Table\MediasTable $Medias
 */
class MediasController extends AppController
{

    public function initialize(){
        parent::initialize();
        $this->loadComponent('Security');
    }

    public function beforeFilter(Event $event)
        {
            parent::beforeFilter($event);
            $this->viewBuilder()->layout('uploader');
            $this->Security->config('unlockedActions', ['index','upload']);
        }

	public function canUploadMedias($ref, $ref_id){
        if(method_exists('App\Controller\AppController', 'canUploadMedias')){
	      	return Parent::canUploadMedias($ref, $ref_id);
           //return Parent::canUploadMedias($ref, $ref_id);
        }else{

            return false;
        }
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index($ref, $ref_id)
    {
        //debug($_GET);die();
    	if (!$this->canUploadMedias($ref, $ref_id)) {
    		die('probleme');
    		//throw new ForbiddenExeption();
    		//throw new NotFoundException('Could not find that post');
    	}
        $this->loadModel($ref);
        $this->set(compact('ref', 'ref_id'));

        if(!in_array('Media', $this->$ref->Behaviors()->loaded())){
            return $this->render('nobehavior');
        }
        $id = isset($this->request->query['id']) ? $this->request->query['id'] : false;
        $medias = $this->$ref->Media->find('all')
        ->where(['ref'=>$ref,'ref_id' => $ref_id]);

        $thumbID = false;
        if($this->$ref->hasField('media_id')){
            $reference = $this->$ref->get($ref_id);
            $thumbID = $reference->media_id;
        }

        $extensions = $this->$ref->Behaviors()->get('Media')->medias['extensions'];
        $editor = isset($this->request->params['named']['editor']) ? $this->request->params['named']['editor'] : false;
        $this->set(compact('id', 'medias', 'thumbID', 'editor', 'extensions', 'ref'));
    }

   /**
    * Upload (Ajax)
    **/
    public function upload($ref, $ref_id){
        $this->viewBuilder()->layout(null);
        $this->autoRender = false;
        if(!$this->canUploadMedias($ref, $ref_id)){
            //throw new ForbiddenException();
            die('probleme');
        }

        $media = $this->Medias->newEntity();
        $file = $_FILES;

        if (empty($file)) {
            die('fichier trop volumineux');
            return false;
        }
        //debug($media);die();
        $model = Inflector::singularize($ref);
        if(isset($_FILES) && !empty($_FILES)) {
            $data = [
                'ref' => $ref ,
                'ref_id' => $ref_id,
                'file' => $_FILES['file']['name'],
                'name' => $_FILES['file']['name']
            ];
            $new_media = $this->Medias->patchEntity($media, $data);
            //debug($media);die();
            $this->Medias->save($new_media, $file);
            if(!empty($new_media->errors())){
                echo json_encode(array('error' => $new_media->errors()));
                return false;
            }
        }
        $this->loadModel($ref);
        $thumbID = false;
        if($this->$ref->hasField('media_id')){
            $reference = $this->$ref->get($ref_id);
            $thumbID = $reference->media_id;
        }
        //debug($this->request->query['id']);
        $editor = isset($this->request->params['named']['editor']) ? $this->request->params['named']['editor'] : false;
        $id = isset($this->request->query['id']) ? $this->request->query['id'] : false;
        $this->set(compact('media', 'thumbID', 'editor', 'id', 'file' , 'ref'));
       
        $this->viewBuilder()->layout('json');
        $this->render('media');
    }


    /**
    * Suppression (Ajax)
    **/
    public function delete($id){
        $this->viewBuilder()->layout(null);
        $this->autoRender = false;
        $media = $this->Medias->get($id);
        if(empty($media)){
            throw new NotFoundException();
        }
        if(!$this->canUploadMedias($media->ref, $media->ref_id)){
            throw new ForbiddenException();
        }
        $this->Medias->delete($media, ['atomic' => false]);
    }

    /**
    * Met l'image à la une
    **/
    public function thumb($id){
        $this->Medias->id = $id;
        $media = $this->Medias->get($id);
        if(empty($media)){
            throw new NotFoundException();
        }
        if(!$this->canUploadMedias($media->ref, $media['Media']['ref_id'])){
            throw new ForbiddenException();
        }
        $ref = $media->ref;
        $ref_id = $media->ref_id;
        $this->loadModel($ref);
        $table = Inflector::pluralize($ref);
        $reference = $this->$table->get($ref_id);
        $reference->media_id = $id;
        $this->$table->save($reference);
        $this->redirect(array('action' => 'index', $table, $ref_id));
    }



    public function order(){
        $this->viewBuilder()->layout(null);
        $this->autoRender = false;
        //debug($this->request->data);
        if(!empty($this->request->data['data']['Media'])){
            $id = key($this->request->data['data']['Media']);
           
            //$this->Media->get($id);
            $media = $this->Medias->get($id);
            $ref = $media->ref;
            //debug($id);die();
            $reference = $media;
            if(!$this->canUploadMedias($reference->ref, $reference->ref_id)){
                throw new ForbiddenException();
            }
            foreach($this->request->data['data']['Media'] as $k => $v){
                $media = $this->Medias->get($k); 
                $media->position = $v;
                $this->Medias->save($media);
            }
        }
    }
}
