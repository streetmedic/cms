<?php defined('SYSPATH') or die('No direct access allowed.');


/**
 * The Archive class...
 */
class Archive
{
    public function __construct(&$page, $params)
    {
        $this->page =& $page;
        $this->params = $params;
        
        switch(count($params))
        {
            case 0: break;
            case 1:
                if (strlen((int) $params[0]) == 4)
                    $this->_archiveBy('year', $params);
                else
                    $this->_displayPage($params[0]);
            break;
            
            case 2:
                $this->_archiveBy('month', $params);
            break;
            
            case 3:
                $this->_archiveBy('day', $params);
            break;
            
            case 4:
                $this->_displayPage($params[3]);
            break;
            
            default:
                page_not_found();
        }
    }
    
    private function _archiveBy($interval, $params)
    {
        $this->interval = $interval;
        
        $conn = Record::getConnection();
        
        $page = $this->page->children(array(
            'where' => "behavior_id = 'archive_{$interval}_index'",
            'limit' => 1
        ), array(), true);
        
        if ($page)
        {
            $this->page = $page;
            $month = isset($params[1]) ? (int)$params[1]: 1;
            $day = isset($params[2]) ? (int)$params[2]: 1;

            $this->page->time = mktime(0, 0, 0, $month, $day, (int)$params[0]);
        }
        else
        {
            page_not_found();
        }
    }
    
    private function _displayPage($slug)
    {
        if( ($this->page = FrontPage::findBySlug($slug, $this->page)) === false )
            page_not_found();
    }
    
    public function get()
    {
        $date = join('-', $this->params);
        
        $pages = $this->page->parent->children(array(
            'where' => "page.created_on LIKE '{$date}%'",
            'order' => 'page.created_on DESC'
        ));
        return $pages;
    }
    
    public function archivesByYear()
    {
        $conn = Record::getConnection();
        
        $out = array();

        $sql = "SELECT DISTINCT(DATE_FORMAT(created_on, '%Y')) FROM ".TABLE_PREFIX."page WHERE parent_id=? AND status_id != ".FrontPage::STATUS_HIDDEN." ORDER BY created_on DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(array($this->page->id));
        
        while ($date = $stmt->fetchColumn())
            $out[] = $date;
        
        return $out;
    }
    
    public function archivesByMonth($year='all')
    {
        $conn = Record::getConnection();
        
        $out = array();
        
        $sql = "SELECT DISTINCT(DATE_FORMAT(created_on, '%Y/%m')) FROM ".TABLE_PREFIX."page WHERE parent_id=? AND status_id != ".FrontPage::STATUS_HIDDEN." ORDER BY created_on DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(array($this->page->id));
        
        while ($date = $stmt->fetchColumn())
            $out[] = $date;
        
        return $out;
    }
    
    public function archivesByDay($year='all')
    {
        $conn = Record::getConnection();
        
        $out = array();
        
        if ($year == 'all') $year = '';
        
        $sql = "SELECT DISTINCT(DATE_FORMAT(created_on, '%Y/%m/%d')) FROM ".TABLE_PREFIX."page WHERE parent_id=? AND status_id != ".FrontPage::STATUS_HIDDEN." ORDER BY created_on DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(array($this->page->id));
        
        while ($date = $stmt->fetchColumn())
            $out[] = $date;
        
        return $out;
    }
	
} // end class Archive


class PageArchive extends FrontPage
{
    protected function setUrl()
    {
        $this->url = trim($this->parent->url . date('/Y/m/d/', strtotime($this->created_on)). $this->slug, '/');
    }
    
    public function title() { return isset($this->time) ? strftime($this->title, $this->time): $this->title; }
    
    public function breadcrumb() { return isset($this->time) ? strftime($this->breadcrumb, $this->time): $this->breadcrumb; }
	
} // end class PageArchive