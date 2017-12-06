<?php

class BotController extends Website_Controller_Action
{

    public function detailAction()
    {
        $this->enableLayout();

    }

    private function processMessage($update) {
        $parameters = $update["result"]["parameters"];
        if($update["result"]["action"] == "startbooking"){
            $this->sendMessage(array(
                "source" => $update["result"]["source"],
                "speech" => "..........TEXT HERE...........",
                "displayText" => "Hotel in " . $parameters["demi_ort"],
                "contextOut" => array()
            ));
        }
    }

    private function sendMessage($parameters) {
        echo json_encode($parameters);
    }

    public function webhookAction() {
//        $this->disableLayout();

        $update_response = file_get_contents("php://input");
        $update = json_decode($update_response, true);
        if (isset($update["result"]["action"])) {
            $this->processMessage($update);
        }
    }
}