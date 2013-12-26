<?php defined( 'SYSPATH' ) or die( 'No direct access allowed.' );

class Controller_Datasources_Section extends Controller_System_Datasource
{	
	public function before()
	{
		if($this->request->action() != 'create')
		{
			$ds_id = (int) $this->request->param('id');
			$this->_get_ds($ds_id);

			if(Acl::check($this->_ds->type().$ds_id.'.section.edit'))
			{
				$this->allowed_actions[] = 'edit';
			}
			
			if(Acl::check($this->_ds->type().$ds_id.'.section.remove'))
			{
				$this->allowed_actions[] = 'remove';
			}
		}

		parent::before();
	}

	public function action_create()
	{
		$type = $this->request->param('id');
		$type = strtolower($type);

		$types = Datasource_Data_Manager::types();
		if( Arr::get($types, $type) === NULL)
		{
			throw new Kohana_Exception('Datasource type :type not found', array(':type' => $type));
		}
		
		if($this->request->method() === Request::POST)
		{
			return $this->_create($type);
		}

		$this->breadcrumbs
				->add(__('Add section :type', array(':type' => Arr::get($types, $type))));
		
		$this->template->content = View::factory('datasource/section/create', array(
			'type' => $type,
			'data' => Flash::get('post_data')
		));
	}
	
	/**
	 * 
	 * @param string $type
	 */
	private function _create($type)
	{
		$section = Datasource_Section::factory($type);
		
		try
		{
			$ds_id = $section->create($this->request->post());
		}
		catch (Validation_Exception $e)
		{
			Messages::errors($e->errors('validation'));
			$this->go_back();
		}

		$this->go( Route::url('datasources', array(
			'directory' => 'datasources',
			'controller' => 'section',
			'action' => 'edit',
			'id' => $ds_id
		)));
	}

	public function action_edit()
	{
		if($this->request->method() === Request::POST)
		{
			return $this->_edit($this->_ds);
		}
		
		$this->breadcrumbs
			->add($this->_ds->name, Route::url('datasources', array(
				'controller' => 'data',
				'directory' => 'datasources',
			)) . URL::query(array('ds_id' => $this->_ds->id()), FALSE))
			->add(__('Edit ' . $this->_ds->name));
		
		$this->template->content = View::factory('datasource/section/edit', array(
			'ds' => $this->_ds
		));
	}
	
	/**
	 * 
	 * @param Datasource_Section $ds
	 */
	private function _edit($ds)
	{
		$array = $this->request->post();
		
		try
		{
			$ds->valid($array);
			$ds->name = Arr::get($array, 'name');
			$ds->description = Arr::get($array, 'description');

			$ds->save();
		}
		catch (Validation_Exception $e)
		{
			Messages::errors($e->errors('validation'));
			$this->go_back();
		}

		// save and quit or save and continue editing?
		if ( $this->request->post('commit') !== NULL )
		{
			$this->go( Route::url('datasources', array(
				'directory' => 'datasources',
				'controller' => 'data'
			)) .  URL::query(array('ds_id' => $ds->id()), FALSE));
		}
		else
		{
			$this->go_back();
		}
	}
	
	public function action_remove()
	{
		$this->_ds->remove();
		$this->go_back();
	}
}