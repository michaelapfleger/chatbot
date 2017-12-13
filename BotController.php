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
    private function getCategory($category) {
        $categories = new Object_DemiCategory_List();
        $categories->addConditionParam('name = ' . $category );
        $categories->setLimit(1);
        return $categories->load();
    }


    private function startBooking($parameters) {
        $town = $this->getTown($parameters["demi_ort"]);
        $category = $this->getCategory($parameters["demi_unterkunftsart"]);
        $list = new Object_DemiAccommodationServiceProvider_List();
        if ($town) {
            $list->addConditionParam('town__id = ' . $town[0]->getId());
        }
        if ($category) {
            $list->addConditionParam('categories LIKE "%,' . $category[0]->getId() . ',%"');
        }
        $list->setOrderKey("name");
        $list->setOrder("ASC");
        $list->setLimit(3);
        return $list->load();
    }

    private function printAcco($accos) {
        $attachments = array();
        foreach ($accos as $acco) {
            $image = $acco->getFirstImage(null, null);
            if ($image) {
                $thumbnail = $image->getThumbnail('demi_responsive_list');
                $thumbnailUrl = "https://www.kleinwalsertal.com" . $thumbnail->getPath();
            }
            $address = $acco->getAddress();
            $description = $acco->getDescription(null, 'de', null);
            $website = $address->getUrl();
            $mGroups = $acco->getMarketingGroups();
            foreach($mGroups as $marketingGroup) {
                if($marketingGroup->getFid() == "85392dfc-f8dc-4aa7-8c85-55b9ba91817b" || $marketingGroup->getFid() == "ca5d024e-d0d3-457e-8c21-7327fde12890"
                    || $marketingGroup->getFid() == "58e7b7e0-2b4a-4d1c-b9ee-3b57d173dbe7") {
                    $mg[$marketingGroup->getFid()] = $marketingGroup;
                }
            }
            $attachment = [
                "color" => "#87B109",
//                "pretext" => "Hier habe ich einige Unterkünfte für dich zusammengestellt:",
                "title" => $acco->getName(),
                "text" => $acco->getCategoryNames(2),
                "fields" => [
                    [
                        "title" => "Adresse",
                        "value" => $address->getAddressLine1() . " " . $address->getAddressLine2() . "\n" . $address->getZipcode() . " " . $address->getTown() . " " . $address->getCity(),
                        "short" => true
                    ],
                    [
                        "title" => "Klassifizierung",
                        "value" => Demi_Website_Helper::desklineStars($acco) ?: "keine",
                        "short" => true
                    ],
                    [
                        "title" => "Webste",
                        "value" => $website ?: "-",
                        "short" => true
                    ],
                    [
                        "title" => $mg["85392dfc-f8dc-4aa7-8c85-55b9ba91817b"] ? "Bestpreisgarantie" : "",
                        "value" => "",
                        "short" => true
                    ],
                    [
                        "title" => "Beschreibung",
                        "value" => str_replace(["&auml;","&uuml;","&ouml;","&szlig;","&ndash;", "&bull;", "&nbsp;"],["ä","ü","ö","ß", "-", "•"," "],strip_tags($description)),
                        "short" => false
                    ],
                ],
                "image_url" => $thumbnailUrl,
                "thumb_url" => "https://www.kleinwalsertal.com/static/img/sprite/mobile/best-price-badge.png",
            ];
            $attachments[] = $attachment;
        }
        return $attachments;
    }

    private function processMessage($update) {
        $parameters = $update["result"]["parameters"];
        if($update["result"]["action"] == "startbooking"){
            $list = $this->startBooking($parameters);
            $attachments = $this->printAcco($list);
            $this->sendMessage(array(
//                "source" => $update["result"]["source"],
//                "speech" => "Hotel in " . $parameters["demi_ort"] . " für " . $parameters["amount_adults"] . " am " . $parameters["date"] . ": " . $name,
                "displayText" => "Hotel in " . $parameters["demi_ort"],
                "data" => [
                    "slack" => [
                        "text" => "*Hier habe ich einige Unterkünfte für dich zusammengestellt:*",
                        "attachments" => $attachments,
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
//        $list = new Object_DemiAccommodationServiceProvider_List();
//        $list->addConditionParam('categories LIKE "%,' . "90507" . ',%"');
//        $list->setOrderKey("name");
//        $list->setOrder("ASC");
//        $list->setLimit(1);
//
//        foreach ($list as $acco) {
//            p_r($acco->getId());
//        }
//
//        die();
        $this->getResponse()->setHeader("X-Robots-Tag", "noindex, nofollow", true);
//        $this->disableLayout();

        $update_response = file_get_contents("php://input");
        $update = json_decode($update_response, true);
        if (isset($update["result"]["action"])) {
            $this->processMessage($update);
        }

    }
}