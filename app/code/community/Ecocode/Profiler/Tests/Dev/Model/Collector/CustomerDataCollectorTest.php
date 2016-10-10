<?php

class Ecocode_Profiler_Tests_Dev_Model_Collector_CustomerDataCollectorTest
    extends TestHelper
{

    public function testCollect()
    {
        $customer = new Mage_Customer_Model_Customer();
        $customer->setData([
            'email'     => 'test@test.com',
            'firstname' => 'first',
            'lastname'  => 'last'
        ]);

        $customerHelper = $this->getMockBuilder('Mage_Customer_Helper_Data')
            ->setMethods(['getCustomer', 'isLoggedIn'])
            ->getMock();

        $customerHelper->method('getCustomer')
            ->willReturn($customer);

        $customerHelper->method('isLoggedIn')
            ->willReturn(false);

        /** @var Ecocode_Profiler_Model_Collector_CustomerDataCollector $collector */
        $collector = $this->getMockBuilder('Ecocode_Profiler_Model_Collector_CustomerDataCollector')
            ->setMethods(['getCustomerHelper'])
            ->getMock();

        $collector->method('getCustomerHelper')
            ->willReturn($customerHelper);

        $collector->collect(
            new Mage_Core_Controller_Request_Http(),
            new Mage_Core_Controller_Response_Http()
        );

        $this->assertFalse($collector->isLoggedIn());

        $this->assertEquals('test@test.com', $collector->getCustomerEmail());
        $this->assertEquals('first last', $collector->getCustomerName());

        $this->assertEquals(1, $collector->getGroupId());
        $this->assertEquals('General', $collector->getGroupCode());


        $this->assertEquals('3', $collector->getTaxClassId());
        $this->assertEquals('Retail Customer', $collector->getTaxClassName());
        $this->assertEquals('CUSTOMER', $collector->getTaxClassType());


    }
}
