<?php

class Ecocode_Profiler_Tests_Dev_Fixtures_ResponseHttp
    extends Mage_Core_Controller_Response_Http
{
    public function canSendHeaders($throw = true)
    {
        return true;
    }
}
