<?php defined('SYSPATH') or die('No direct access allowed.');

class Behavior_Archive extends Behavior_Abstract
{

	public function execute()
	{
		
	}

	private function _archiveBy($interval, $params)
    {
        $this->interval = $interval;

        $page = $this->page->children(array(
            'where' => array(
				array('behavior_id', '=', 'archive_' . $interval . '_index')
			),
            'limit' => 1
        ), array(), TRUE);
        
        if ($page)
        {
            $this->page = $page;
            $month = isset($params[1]) ? (int)$params[1]: 1;
            $day = isset($params[2]) ? (int)$params[2]: 1;

            $this->page->time = mktime(0, 0, 0, $month, $day, (int)$params[0]);
        }
        else
        {
            Model_Page_Front::not_found();
        }
    }
    
    private function _displayPage($slug)
    {
        if( ($this->page = Model_Page_Front::findBySlug($slug, $this->page)) === false )
		{
            Model_Page_Front::not_found();
		}
    }
    
    public function get()
    {
        $date = join('-', $this->params);
        
        $pages = $this->page->parent->children(array(
            'where' => array(array('page.created_on', 'like', $date . '%')),
            'order' => array(array('page.created_on', 'desc'))
        ));

        return $pages;
    }
    
    public function archivesByYear()
    {
		return DB::select(array(DB::expr( 'DATE_FORMAT('. Database::instance()->quote_column('created_on').', "%Y")' ), 'date'))
			->distinct(TRUE)
			->from(Model_Page::TABLE_NAME)
			->where('parent_id', '=', $this->_page->id)
			->where('status_id', '!=', Model_Page::STATUS_HIDDEN)
			->order_by( 'created_on', 'desc' )
			->execute()
			->as_array(NULL, 'date');
    }
    
    public function archivesByMonth()
    {
		return DB::select(array(DB::expr( 'DATE_FORMAT('. Database::instance()->quote_column('created_on').', "%Y/%m")' ), 'date'))
			->distinct(TRUE)
			->from(Model_Page::TABLE_NAME)
			->where('parent_id', '=', $this->_page->id)
			->where('status_id', '!=', Model_Page::STATUS_HIDDEN)
			->order_by( 'created_on', 'desc' )
			->execute()
			->as_array(NULL, 'date');
    }
    
    public function archivesByDay()
    {
		return DB::select(array(DB::expr( 'DATE_FORMAT('. Database::instance()->quote_column('created_on').', "%Y/%m/%d")' ), 'date'))
			->distinct(TRUE)
			->from(Model_Page::TABLE_NAME)
			->where('parent_id', '=', $this->_page->id)
			->where('status_id', '!=', Model_Page::STATUS_HIDDEN)
			->order_by( 'created_on', 'desc' )
			->execute()
			->as_array(NULL, 'date');
    }
	
}