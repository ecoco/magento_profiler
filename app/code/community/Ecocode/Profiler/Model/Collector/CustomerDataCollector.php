<?php

class Ecocode_Profiler_Model_Collector_CustomerDataCollector
    extends Ecocode_Profiler_Model_Collector_AbstractDataCollector
{
    /**
     * {@inheritdoc}
     */
    public function collect(Mage_Core_Controller_Request_Http $request, Mage_Core_Controller_Response_Http $response, \Exception $exception = null)
    {
        /** @var  $helper */
        $helper = Mage::helper('customer');
        /** @var Mage_Customer_Model_Customer $customer */
        $customer   = $helper->getCustomer();
        $group      = Mage::getModel('customer/group')->load($customer->getGroupId());
        $taxClass   = Mage::getModel('tax/class')->load($group->getTaxClassId());
        $this->data = [
            'logged_in'      => $helper->isLoggedIn(),
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
        return $this->data['logged_in'];
    }

    public function getCustomerEmail()
    {
        return $this->data['customer_email'];
    }

    public function getCustomerName()
    {
        return $this->data['customer_name'];
    }

    public function getGroupId()
    {
        return $this->data['group_id'];
    }

    public function getGroupCode()
    {
        return $this->data['group_code'];
    }


    public function getTaxClassName()
    {
        return $this->data['tax_class_name'];
    }

    public function getTaxClassId()
    {
        return $this->data['tax_class_id'];
    }

    public function getTaxClassType()
    {
        return $this->data['tax_class_type'];
    }


    public function getName()
    {
        return 'customer';
    }
}