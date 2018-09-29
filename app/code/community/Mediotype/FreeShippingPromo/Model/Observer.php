<?php
/**
 *
 * @author  Joel    @Mediotype
 */
class Mediotype_FreeShippingPromo_Model_Observer {

	/**
     * Prepare salesrule form. Add field to specify reward points delta
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function prepareSalesruleForm(Varien_Event_Observer $observer)
    {
        $form = $observer->getEvent()->getForm();
        $fieldset = $form->getElement('action_fieldset');
        $fieldset->addField('creates_free_shipping', 'select', array(
            'label'     => Mage::helper('salesrule')->__('Free Shipping (USPS - First Class Mail and USPS - First-Class Mail International Large Envelope) With Coupon?'),
            'title'     => Mage::helper('salesrule')->__('Free Shipping (USPS - First Class Mail and USPS - First-Class Mail International Large Envelope) With Coupon?'),
            'name'      => 'creates_free_shipping',
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        return $this;

    }

	public function checkFreeShippingRuleAndApply(Varien_Event_Observer $observe)
    {
	    $ckbPromoHelper = Mage::helper('med_promo');

	    if($ckbPromoHelper->doesFreeShippingCouponExist()){
		    $address = $observe->getData();
	        $address->setShippingAmount(0);
	    }

        return $this;
    }

}