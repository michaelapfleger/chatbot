<?php

class BotController extends Website_Controller_Action
{

    public function detailAction()
    {
        $this->enableLayout();

    }
    public function webhookAction() {
//        $this->disableLayout();

        $hotels = "Ich kann dir folgende Unterkünfte in " . " vorschlagen:\r\n";
        $hotels .= "Hotel Hillinger\r\n";
        $hotels .= "arte Hotel Wien\r\n";
        $hotels .= "Startlight Suiten Wien\r\n";
        $hotels .= "Star Inn Hotel\r\n";

        header('Content-Type: application/json');
        $data = ["speech" => $hotels, "displayText" => $hotels];
        echo json_encode($data);
    }
}