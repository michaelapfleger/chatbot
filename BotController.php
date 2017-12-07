<?php

class BotController extends Website_Controller_Action
{

    public function detailAction()
    {
        $this->enableLayout();

    }


    private function getTown($town) {
        $towns = new Object_DemiTown_List();
        $towns->addConditionParam('name LIKE "%' . $town . '%"');
        $towns->setLimit(1);
        return $towns->load();
    }


    private function startBooking($parameters) {
        $town = $this->getTown($parameters["demi_ort"]);
        $list = new Object_DemiAccommodationServiceProvider_List();
        if ($town) {
            $list->addConditionParam('town__id = ' . $town[0]->getId());
        }
        $list->setOrderKey("name");
        $list->setOrder("ASC");
        $list->setLimit(5);
        $name = " --- ";
        foreach($list->load() as $item) {
            $name .= $item->getName() . " --- ";
        }
        return $name;
    }

    private function printAcco($acco) {
        $attachment = [
            "color" => "#87B109",
            "pretext" => "Hier habe ich einige Unterkünfte für dich zusammengestellt:",
            "title" => $acco->getName(),
            "text" => "Subline für Hotel 1",
            "fields" => [
                [
                    "title" => "Kinderfreundlich",
                    "value" => "High",
                    "short" => false
                ]
            ],
            "image_url" => "http://www.freeridecamps.at/wp-content/uploads/2016/10/Kleinwalsertal-01.png",
            "thumb_url" => "http://www.freeridecamps.at/wp-content/uploads/2016/10/Kleinwalsertal-01.png",
            "footer" => "Kleinwalsertal",
            "footer_icon" => "http://www.freeridecamps.at/wp-content/uploads/2016/10/Kleinwalsertal-01.png",
        ];
    }

    private function processMessage($update) {
        $parameters = $update["result"]["parameters"];
        if($update["result"]["action"] == "startbooking"){
            $name = $this->startBooking($parameters);
            $this->sendMessage(array(
//                "source" => $update["result"]["source"],
                "speech" => "Hotel in " . $parameters["demi_ort"] . " für " . $parameters["amount_adults"] . " am " . $parameters["date"] . ": " . $name,
                "displayText" => "Hotel in " . $parameters["demi_ort"],
                "data" => [
                    "slack" => [
                        "attachments" => [
                            [
                            ]
                        ]
                    ]
                ]
//                "contextOut" => array()
            ));
        }
    }

    private function sendMessage($parameters) {
        echo json_encode($parameters);
    }

    public function webhookAction() {
        $this->getResponse()->setHeader("X-Robots-Tag", "noindex, nofollow", true);
//        $this->disableLayout();

        $update_response = file_get_contents("php://input");
        $update = json_decode($update_response, true);
        if (isset($update["result"]["action"])) {
            $this->processMessage($update);
        }

    }
}