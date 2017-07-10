<?php
/**
 * @category    Fishpig
 * @package    Fishpig_SeoTagUrls
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_SeoTagUrls_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Retrieve the front name used by the extension
	 *
	 * @return string
	 */
	public function getFrontName()
	{
		return Mage::getStoreConfig('seotagurls/settings/front_name');
	}

	/**
	 * Convert a tag ID to it's slug
	 *
	 * @param int $tagId
	 * @return false|string
	 */
	public function getSlugByTagId($tagId)
	{
		$resource = Mage::getSingleton('core/resource');
		$read = $resource->getConnection('core_read');
		
		$select = $read->select()
			->from($resource->getTableName('tag/tag'), 'name')
			->where('tag_id=?', $tagId)
			->limit(1);
			
		if (($name = $read->fetchOne($select)) !== false) {
			return $this->_convertStringToSlug($name);
		}
		
		return false;
	}
	
	/**
	 * Convert a slug to it's tag ID
	 *
	 * @param string $slug
	 * @return false|int
	 */
	public function getTagIdBySlug($slug)
	{
		$resource = Mage::getSingleton('core/resource');
		$read = $resource->getConnection('core_read');
		
		$select = $read->select()
			->from($resource->getTableName('tag/tag'), array('tag_id', 'name'));
			
		if ($results = $read->fetchAll($select)) {
			foreach($results as $result) {
				if ($this->_convertStringToSlug($result['name']) === $slug) {
					return $result['tag_id'];
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Convert a normal string to cleaned slug
	 *
	 * @param string $string
	 * @return string
	 */
	protected function _convertStringToSlug($string)
	{
		return trim(str_replace(array('--', '--', '--', '--'), '-', preg_replace('/([^a-z0-9-]{1,})/', '-', strtolower(trim($string)))), '-');
	}
	
	/**
	 * Inject the tag links into the HTML response
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function injectLinksIntoResponseObserver(Varien_Event_Observer $observer)
	{
		// Get HTML from response object
		$html = $observer->getEvent()
			->getFront()
				->getResponse()
					->getBody();
		
		if (preg_match_all('/href="([^"]{1,}tag\/product\/list\/tagId\/([0-9]{1,})[\/]{0,})"/iU', $html, $matches)) {
			$urls = array_combine($matches[2], $matches[1]);

			foreach($urls as $tagId => $url) {
				if (($tag = $this->getSlugByTagId($tagId)) !== false) {
					$html = str_replace($url, $this->getSeoTagUrl($tag), $html);
				}
			}
		}
		
		// Set new HTML string to response object
		$observer->getEvent()
			->getFront()
				->getResponse()
					->setBody($html);
		
		return $this;
	}
	
	/**
	 * Get the URL for the tag slug
	 *
	 * @param string $slug
	 * @return string
	 */
	public function getSeoTagUrl($slug)
	{
		return Mage::getSingleton('core/url')->getUrl('', array(
			'_direct' => $this->getFrontName() . '/' . $slug . '/'
		));
	}
}
