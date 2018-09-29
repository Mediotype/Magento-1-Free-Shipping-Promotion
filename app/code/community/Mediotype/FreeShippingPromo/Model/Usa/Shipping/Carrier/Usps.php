<?php
/**
 *
 * @author  Joel    @Mediotype
 */
class Mediotype_FreeShippingPromo_Model_Usa_Shipping_Carrier_Usps extends Mage_Usa_Model_Shipping_Carrier_Usps{

    /**
     * Parse calculated rates
     *
     * @link http://www.usps.com/webtools/htm/Rate-Calculators-v2-3.htm
     * @param string $response
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _parseXmlResponse($response)
    {
        $r = $this->_rawRequest;
        $costArr = array();
        $priceArr = array();
        if (strlen(trim($response)) > 0) {
            if (strpos(trim($response), '<?xml') === 0) {
                if (strpos($response, '<?xml version="1.0"?>') !== false) {
                    $response = str_replace(
                        '<?xml version="1.0"?>',
                        '<?xml version="1.0" encoding="ISO-8859-1"?>',
                        $response
                    );
                }
                $xml = simplexml_load_string($response);

                if (is_object($xml)) {
                     $allowedMethods = explode(',', $this->getConfigData('allowed_methods'));
                     $serviceCodeToActualNameMap = array();
                     /**
                      * US Rates
                      */
                      if ($this->_isUSCountry($r->getDestCountryId())) {
                          if (is_object($xml->Package) && is_object($xml->Package->Postage)) {
                             foreach ($xml->Package->Postage as $postage) {
                                $serviceName = $this->_filterServiceName((string)$postage->MailService);
                                $_serviceCode = $this->getCode('method_to_code', $serviceName);
                                $serviceCode = $_serviceCode ? $_serviceCode : (string)$postage->attributes()->CLASSID;
                                $serviceCodeToActualNameMap[$serviceCode] = $serviceName;
                                if (in_array($serviceCode, $allowedMethods)) {
                                     $costArr[$serviceCode] = (string)$postage->Rate;
                                     $priceArr[$serviceCode] = $this->getMethodPrice(
                                         (string)$postage->Rate,
                                         $serviceCode
                                     );
                                 }
                            }
                            asort($priceArr);
                        }
                     }
                     /**
                      * International Rates
                      */
                     else {
                        if (is_object($xml->Package) && is_object($xml->Package->Service)) {
                            foreach ($xml->Package->Service as $service) {
                                $serviceName = $this->_filterServiceName((string)$service->SvcDescription);
                                $serviceCode = 'INT_' . (string)$service->attributes()->ID;
                                $serviceCodeToActualNameMap[$serviceCode] = $serviceName;
                                    if (in_array($serviceCode, $allowedMethods)) {
                                        $costArr[$serviceCode] = (string)$service->Postage;
                                        $priceArr[$serviceCode] = $this->getMethodPrice(
                                         (string)$service->Postage,
                                            $serviceCode);
                                }
                            }
                            asort($priceArr);
                        }
                    }
                }

        $result = Mage::getModel('shipping/rate_result');
        if (empty($priceArr)) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('usps');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {

            /**
             *
             */
            $promoHelper = Mage::helper('med_promo');
	        $freeShippingOptionApplied = $promoHelper->doesFreeShippingCouponExist();
            /**
             *
             */

            foreach ($priceArr as $method => $price) {
                $rate = Mage::getModel('shipping/rate_result_method');
                $rate->setCarrier('usps');
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                 $rate->setMethodTitle(
                     isset($serviceCodeToActualNameMap[$method])
                         ? $serviceCodeToActualNameMap[$method]
                         : $this->getCode('method', $method)
                );
                $rate->setCost($costArr[$method]);

                /**
                 *
                 */
                $freeMethodsWithCoupon  = array("INT_14","0_FCP");
                if($freeShippingOptionApplied && in_array($method,$freeMethodsWithCoupon)){
                    $rate->setPrice(0);
                } else {
                    $rate->setPrice($price);
                }
                /**
                 *
                 */
                $rate->setPrice($price);
                $result->append($rate);
            }
        }

        return $result;
            }
        }

    }
}