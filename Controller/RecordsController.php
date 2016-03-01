<?php
/**
 * iroha Board Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2016 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohasoft.jp/irohaboard
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses('AppController', 'Controller');
App::uses('RecordsQuestion', 'RecordsQuestion');

/**
 * Records Controller
 *
 * @property Record $Record
 * @property PaginatorComponent $Paginator
 */
class RecordsController extends AppController
{

	public $components = array(
			'Paginator',
			'Search.Prg'
	);

	public $presetVars = true;

	public $paginate = array();
	
	// 検索対象のフィルタ設定
	/*
	 * public $filterArgs = array( array('name' => 'name', 'type' => 'value',
	 * 'field' => 'User.name'), array('name' => 'username', 'type' => 'like',
	 * 'field' => 'User.username'), array('name' => 'title', 'type' => 'like',
	 * 'field' => 'Content.title') );
	 */
	public function admin_index()
	{
		// 検索条件設定
		$this->Prg->commonProcess();
		
		$this->Paginator->settings['conditions'] = $this->Record->parseCriteria($this->Prg->parsedParams());
		
		// debug($this->Prg);
		
		// 検索条件取得
		// $conditions = $this->Record->parseCriteria($this->passedArgs);
		
		/*
		debug($this->request->data);
		
		$this->Paginator->settings = array(
			"conditions" => array
				(
					"AND" => array
						(
							"User.name LIKE" => "%".$this->request->data["Record"]["username"]."%",
							"Course.title LIKE" => "%".$this->request->data["Record"]["coursetitle"]."%",
							"Content.title LIKE" => "%".$this->request->data["Record"]["contenttitle"]."%"
						)
				),
			'limit' => 10
		);
		
		
		debug($this->request->query['group_id']);
		*/
		$this->Record->recursive = 0;
		$this->set('records', $this->Paginator->paginate());
		
		$this->set('groups', $this->Group->find( 'list', array( 'fields' => array( 'id', 'title'))));
	}

	public function view($id = null)
	{
		if (! $this->Record->exists($id))
		{
			throw new NotFoundException(__('Invalid record'));
		}
		$options = array(
				'conditions' => array(
						'Record.' . $this->Record->primaryKey => $id
				)
		);
		$this->set('record', $this->Record->find('first', $options));
	}

	public function add($id, $is_complete, $understanding)
	{
		$this->Record->create();
		$data = array(
//				'group_id' => $this->Session->read('Auth.User.Group.id'),
				'user_id' => $this->Session->read('Auth.User.id'),
				'course_id' => $this->Session->read('Iroha.course_id'),
				'content_id' => $id,
				'understanding' => $understanding,
				'is_complete' => $is_complete
		);
		
		if ($this->Record->save($data))
		{
			$this->Flash->success(__('学習履歴を保存しました'));
			return $this->redirect(
					array(
							'controller' => 'contents',
							'action' => 'index',
							$this->Session->read('Iroha.course_id')
					));
		}
		else
		{
			$this->Flash->error(__('The record could not be saved. Please, try again.'));
		}
	}

	public function record($id, $record, $details)
	{
		$this->Record->create();
		
		$data = array(
//				'group_id' => $this->Session->read('Auth.User.Group.id'),
				'user_id' => $this->Session->read('Auth.User.id'),
				'course_id' => $this->Session->read('Iroha.course_id'),
				'content_id' => $id,
				'full_score' => $record['full_score'],
				'pass_score' => $record['pass_score'],
				'score' => $record['score'],
				'is_passed' => $record['is_passed'],
				'study_sec' => $record['study_sec'],
				'is_complete' => 1
		);
		
		if ($this->Record->save($data))
		{
			$this->RecordsQuestion = new RecordsQuestion();
			
			foreach ($details as $detail)
			:
				$this->RecordsQuestion->create();
				$detail['record_id'] = $this->Record->getLastInsertID();
				$this->RecordsQuestion->save($detail);
			endforeach
			;
		}
	}

	public function edit($id = null)
	{
		if (! $this->Record->exists($id))
		{
			throw new NotFoundException(__('Invalid record'));
		}
		
		if ($this->request->is(array(
				'post',
				'put'
		)))
		{
			if ($this->Record->save($this->request->data))
			{
				$this->Flash->success(__('The record has been saved.'));
				return $this->redirect(array(
						'action' => 'index'
				));
			}
			else
			{
				$this->Flash->error(__('The record could not be saved. Please, try again.'));
			}
		}
		else
		{
			$options = array(
					'conditions' => array(
							'Record.' . $this->Record->primaryKey => $id
					)
			);
			$this->request->data = $this->Record->find('first', $options);
		}
		
		$groups = $this->Record->Group->find('list');
		$courses = $this->Record->Course->find('list');
		$users = $this->Record->User->find('list');
		$contents = $this->Record->Content->find('list');
		$this->set(compact('groups', 'courses', 'users', 'contents'));
	}

	public function admin_delete($id = null)
	{
		$this->Record->id = $id;
		
		if (! $this->Record->exists())
		{
			throw new NotFoundException(__('Invalid record'));
		}
		
		$this->request->allowMethod('post', 'delete');
		
		if ($this->Record->delete())
		{
			$this->Flash->success(__('The record has been deleted.'));
		}
		else
		{
			$this->Flash->error(__('The record could not be deleted. Please, try again.'));
		}
		return $this->redirect(array(
				'action' => 'index'
		));
	}
}
