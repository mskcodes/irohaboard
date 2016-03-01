<?php
/**
 * iroha Board Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2016 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohasoft.jp/irohaboard
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses('AppModel', 'Model');

/**
 * Content Model
 *
 * @property Group $Group
 * @property Course $Course
 * @property User $User
 * @property Record $Record
 */
class Content extends AppModel
{

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	public $validate = array(
			'group_id' => array(
					'numeric' => array(
							'rule' => array(
									'numeric'
							)
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										)
			),
			'course_id' => array(
					'numeric' => array(
							'rule' => array(
									'numeric'
							)
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										)
			),
			'user_id' => array(
					'numeric' => array(
							'rule' => array(
									'numeric'
							)
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										)
			),
			'title' => array(
					'notBlank' => array(
							'rule' => array(
									'notBlank'
							)
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										)
			),
			'kind' => array(
					'notBlank' => array(
							'rule' => array(
									'notBlank'
							)
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										)
			),
			'sort_no' => array(
					'numeric' => array(
							'rule' => array(
									'numeric'
							)
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										)
			),
			'file'=>array(
					'rule1' => array(
					 //�g���q�̎w��
					 'rule' => array('extension',array('jpg','jpeg','gif','png')),
					 'message' => '�摜�ł͂���܂���B',
					 'allowEmpty' => true,
						),
					'rule2' => array(
					 //�摜�T�C�Y�̐���
					 'rule' => array('fileSize', '<=', '500000'),
					 'message' => '�摜�T�C�Y��500KB�ȉ��ł��肢���܂�',
					)
			),
	);

	public function getContentRecord($user_id, $course_id)
	{
		$sql = <<<EOF
 SELECT Content.*, first_date, last_date, is_passed, record_id,
       (SELECT understanding
          FROM ib_records h1
         WHERE h1.content_id = Content.id
           AND h1.user_id    =:user_id
         ORDER BY created
          DESC LIMIT 1) as understanding
   FROM ib_contents Content
   LEFT OUTER JOIN
       (SELECT h.content_id, h.user_id,
               MAX(DATE_FORMAT(created, '%Y/%m/%d')) as last_date,
               MIN(DATE_FORMAT(created, '%Y/%m/%d')) as first_date,
			   MAX(is_passed) as is_passed,
			   MAX(id) as record_id
		 FROM ib_records h
         WHERE h.user_id    =:user_id
		   AND h.course_id  =:course_id
         GROUP BY h.content_id, h.user_id) Record
     ON Record.content_id  = Content.id
    AND Record.user_id     =:user_id
  WHERE Content.course_id  =:course_id
  ORDER BY Content.sort_no
EOF;
		// debug($user_id);

		$params = array(
//				'group_id' => $group_id,
				'user_id' => $user_id,
				'course_id' => $course_id
		);

		$data = $this->query($sql, $params);

		return $data;
	}
	// The Associations below have been created with all possible keys, those
	// that are not needed can be removed


	public function setOrder($id_list)
	{
		for($i=0; $i< count($id_list); $i++)
		{
			$sql = "UPDATE ib_contents SET sort_no = :sort_no WHERE id= :id";

			$params = array(
					'sort_no' => ($i+1),
					'id' => $id_list[$i]
			);

			$this->query($sql, $params);
		}
	}
	/**
	 * belongsTo associations
	 *
	 * @var array
	 */
	public $belongsTo = array(
			'Group' => array(
					'className' => 'Group',
					'foreignKey' => 'group_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			),
			'Course' => array(
					'className' => 'Course',
					'foreignKey' => 'course_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			),
			'User' => array(
					'className' => 'User',
					'foreignKey' => 'user_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			)
	);

	/**
	 * hasMany associations
	 *
	 * @var array
	 */
	public $hasMany = array(
			'Record' => array(
					'className' => 'Record',
					'foreignKey' => 'content_id',
					'dependent' => false,
					'conditions' => '',
					'fields' => '',
					'order' => '',
					'limit' => '',
					'offset' => '',
					'exclusive' => '',
					'finderQuery' => '',
					'counterQuery' => ''
			)
	);
}
