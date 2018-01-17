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

        if (is_array($category)) {
            $ids = [];
            foreach ($category as $c) {
                if ($c == "keine bevorzugte Unterkunftsart") {
                    return null;
                }
                $categories = new Object_DemiCategory_List();
                $categories->addConditionParam('name LIKE "%' . $c . '%"');
                $categories->setOrderKey('order');
                $categories->setOrder("ASC");
                $categories->setLimit(1);
                $ids[] = $categories->load()[0];
            }
            return $ids;

        } else {
            if ($category == "keine bevorzugte Unterkunftsart") {
                return null;
            }
            $categories = new Object_DemiCategory_List();
            $categories->addConditionParam('name LIKE "%' . $category . '%"'); // hier vl auf = umstellen
            $categories->setLimit(1);
            return $categories->load()[0];
        }
    }

    private function getStars($param) {
        // mindestens 3 sterne / maximal 3 sterne auch noch  behandeln
        $stars = new Object_DemiStars_List();
        $stars->addConditionParam('name LIKE "%' . $param . '%"');
        $stars->setLimit(1);
        return $stars->load();
    }

    private function getHolidayThemes($param) {
        $stars = new Object_DemiHolidayTheme_List();
        return $stars;
    }


    private function startBooking($parameters) {
        $town = $this->getTown($parameters["demi_ort"]);
        $category = $this->getCategory($parameters["unterkunftsart"]);
        $stars = $this->getStars($parameters["demi_stars"]);
        $holidayThemes = $this->getHolidayThemes($parameters["interests"]);
        $list = new Object_DemiAccommodationServiceProvider_List();
        if ($town) {
            $list->addConditionParam('town__id = ' . $town[0]->getId());
        }
        if ($category) {
            if (count($category) > 1) {
                foreach($category as $c) {
                    $categoryQuery[] = 'categories LIKE "%,' . $c->getId() . ',%"';
                }
                $list->addConditionParam('(' . implode("OR", $categoryQuery) . ')');

            } else {
                $list->addConditionParam('categories LIKE "%,' . $category[0]->getId() . ',%"');
            }
        }
        if ($stars) {
//            $list->addConditionParam('stars__id = ' . $stars[0]->getId());
        }
        if ($holidayThemes) {
//            $list->addConditionParam('stars__id = ' . $stars[0]->getId());
        }
        $list->setOrderKey("name");
        $list->setOrder("ASC");
        $list->setLimit(3);
        return $list->load();
    }

    private function printAcco($accos) {
        $attachments = array();
        $i = 0;
        $color = [ "#87B109", "#ffda29", "#2D9EE0"];
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
            $mg = null;
            foreach($mGroups as $marketingGroup) {
                if($marketingGroup->getFid() == "85392dfc-f8dc-4aa7-8c85-55b9ba91817b" || $marketingGroup->getFid() == "ca5d024e-d0d3-457e-8c21-7327fde12890"
                    || $marketingGroup->getFid() == "58e7b7e0-2b4a-4d1c-b9ee-3b57d173dbe7") {
                    $mg[$marketingGroup->getFid()] = $marketingGroup;
                }
            }
            $attachment = [
//                "id" => $acco->getId(),
                "color" => $color[$i],
                "title" => $acco->getName(),
                "type" => $acco->getCategoryNames(2),
                "address" => $address->getAddressLine1() . " " . $address->getAddressLine2() . "\n" . $address->getZipcode() . " " . $address->getTown() . " " . $address->getCity(),
                "classification" => Demi_Website_Helper::desklineStars($acco) ?: "",
                "website" => $website ?: "-",
                "description" => strip_tags($description),

                "fields" => [
                    [
                        "title" => ($mg && $mg["85392dfc-f8dc-4aa7-8c85-55b9ba91817b"]) ? "Bestpreisgarantie" : "",
                        "value" => "",
                        "short" => true
                    ],
                    [
                        "title" => ($mg && $mg["ca5d024e-d0d3-457e-8c21-7327fde12890"]) ? "Sommer Bergbahn inklusive" : "",
                        "value" => "",
                        "short" => true
                    ],
                    [
                        "title" => ($mg && $mg["58e7b7e0-2b4a-4d1c-b9ee-3b57d173dbe7"]) ? "Übernachtung inkl. Skipass" : "",
                        "value" => "",
                        "short" => true
                    ],

                ],
                "image_url" => $thumbnailUrl,
                "thumb_url" => "https://www.kleinwalsertal.com/static/img/sprite/mobile/best-price-badge.png",
            ];
            $attachments[] = $attachment;
            $i++;
        }
        return $attachments;
    }

    private function processMessage($update) {
        $parameters = $update["result"]["parameters"];
        $action = $update["result"]["action"];
        if(1 == 1 || $action == "booking.not.flexible" || $action == "stars.unterkunft"){
            $list = $this->startBooking($parameters);

            if (count($list) > 2) {
                if ($action == "stars.unterkunft") {
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "Es gibt zu viel Hotels",
                            "attachments" => null,

                        ],
                        "speech" => "toomanyhotelsafterstars"
                    ));
                } else if ($action == "interests") {
//                    $this->sendMessage(array(
//                        "data" => [
//                            "text" => "Es gibt zu viel Hotels",
//                            "attachments" => null,
//
//                        ],
//                        "speech" => "toomanyhotelsafterinterests"
//                    ));
                    $attachments = $this->printAcco($list);
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "Hier habe ich einige Unterkünfte nach deinen Wünschen für dich zusammengestellt:",
                            "attachments" => $attachments,

                        ],
                        "speech" => "hotellist"
                    ));
                } else {
                    $speech = "toomanyhotels";
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "Es gibt zu viel Hotels",
                            "attachments" => null,

                        ],
                        "speech" => "toomanyhotels"
                    ));
                }
            } else {
                $attachments = $this->printAcco($list);
                $this->sendMessage(array(
                    "data" => [
                        "text" => "Hier habe ich einige Unterkünfte für dich zusammengestellt: x",
                        "attachments" => $attachments,

                    ],
                    "speech" => "hotellist"
                ));
            }
//            $this->sendMessage(array(
//                "source" => $update["result"]["source"],
//                "speech" => "Hotel in " . $parameters["demi_ort"] . " für " . $parameters["amount_adults"] . " am " . $parameters["date"] . ": " . $name,
//                "displayText" => "Hotel in " . $parameters["demi_ort"],
//                "data" => [
//                        "text" => "Hier habe ich einige Unterkünfte für dich zusammengestellt:",
//                        "attachments" => $attachments,
//
//                ],
//                "speech" => $speech
//                "contextOut" => array()
//            ));
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