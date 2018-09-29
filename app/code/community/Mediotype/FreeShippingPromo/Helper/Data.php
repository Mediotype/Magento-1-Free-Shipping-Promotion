<?php
/**
 * @author	Joel	@Mediotype
 */ 
class Mediotype_FreeShippingPromo_Helper_Data extends Mage_Core_Helper_Abstract {

	public function doesFreeShippingCouponExist()
	{
		$quote = Mage::getSingleton('checkout/session')->getQuote(); /** @var @var $quote Mage_Sales_Model_Quote */

		if(!$quote){
			return false;
		}

		$couponCode = $quote->getCouponCode();

		if(!$couponCode){
			return false;
		}

	    if($couponCode){

			$coupon     = Mage::getModel('salesrule/coupon')->loadByCode($couponCode);

		    if(!$coupon){
			    return false;
		    }

		    $rule       = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());

		    if(!$rule){
			    return false;
		    }
		    $cfs        =   $rule->getCreatesFreeShipping();
		    if($cfs == 1){
				return true;
		    }
	    }

		return false;
	}

}