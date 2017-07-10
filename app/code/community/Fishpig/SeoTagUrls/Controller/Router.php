<?php
/**
 * @category    Fishpig
 * @package    Fishpig_SeoTagUrls
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_SeoTagUrls_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
	/**
	 * Initialize Controller Router
	 *
	 * @param Varien_Event_Observer $observer
	*/
	public function initControllerRouters(Varien_Event_Observer $observer)
	{
		$observer->getEvent()->getFront()->addRouter('seotagurls', $this);
	}

    /**
     * Validate and Match the route against the module
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {
    	$helper = Mage::helper('seotagurls');
    	$urlKey = trim($request->getPathInfo(), '/');

    	$frontName = $helper->getFrontName();
    	
    	if (substr($urlKey, 0, strlen($frontName)) !== $frontName) {
			return false;
    	}

		$tagSlug = urldecode(substr($urlKey, strlen($frontName)+1));

		if (($tagId = $helper->getTagIdBySlug($tagSlug)) === false) {
			return false;
		}

		$request->setModuleName('tag')
			->setControllerName('product')
			->setActionName('list')
			->setParam('tagId', $tagId);
		
		$request->setAlias(
			Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
			$urlKey
		);

		return true;
	}
}
