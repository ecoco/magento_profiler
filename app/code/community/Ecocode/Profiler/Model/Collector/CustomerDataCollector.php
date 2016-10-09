<?php

/**
 * Class Ecocode_Profiler_Model_Collector_CustomerDataCollector
 */
class Ecocode_Profiler_Model_Collector_CustomerDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        $customerHelper = $this->getCustomerHelper();

        $customer = $customerHelper->getCustomer();

        $group    = Mage::getModel('customer/group')->load($customer->getGroupId());
        $taxClass = Mage::getModel('tax/class')->load($group->getTaxClassId());

        $this->data = [
            'logged_in'      => $customerHelper->isLoggedIn(),
            'customer_email' => $customer->getEmail(),
            'customer_name'  => $customer->getName(),
            'group_id'       => $group->getId(),
            'group_code'     => $group->getCustomerGroupCode(),
            'tax_class_id'   => $taxClass->getId(),
            'tax_class_name' => $taxClass->getClassName(),
            'tax_class_type' => $taxClass->getClassType()
        ];
    }

    public function isLoggedIn()
    {
        return $this->getData('logged_in');
    }

    public function getCustomerEmail()
    {
        return $this->getData('customer_email');
    }

    public function getCustomerName()
    {
        return $this->getData('customer_name');
    }

    public function getGroupId()
    {
        return $this->getData('group_id');
    }

    public function getGroupCode()
    {
        return $this->getData('group_code');
    }


    public function getTaxClassName()
    {
        return $this->getData('tax_class_name');
    }

    public function getTaxClassId()
    {
        return $this->getData('tax_class_id');
    }

    public function getTaxClassType()
    {
        return $this->getData('tax_class_type');
    }

    /**
     * @codeCoverageIgnore
     * @return Mage_Customer_Helper_Data
     */
    public function getCustomerHelper()
    {
        return Mage::helper('customer');
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getName()
    {
        return 'customer';
    }
}
