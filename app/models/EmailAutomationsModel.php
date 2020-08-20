<?php 
/**
 * EmailAutomations model
 *
 * @version 1.0
 * @author RHA <contato@rhamarketing.com.br> 
 * 
 */
class EmailAutomationsModel extends DataList
{	
	/**
	 * Initialize
	 */
	public function __construct()
	{
		$this->setQuery(DB::table(TABLE_PREFIX.TABLE_EMAILAUTOMATION));
	}
}
