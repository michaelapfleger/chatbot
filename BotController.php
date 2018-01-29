<?php

class Demi2015_BotController extends Demi2015_Website_Controller_Action
{

    public $params = null;

    public function init()
    {
        parent::init();

        Deskline_Init::init();

        $this->view->availableNightRanges = $this->availableNightRanges;

        $this->view->zendDateFormatShort = Zend_Date::DAY.'.'.Zend_Date::MONTH.'.'.Zend_Date::YEAR;
        $this->view->zendDateFormatLong = Zend_Date::WEEKDAY.', '.Zend_Date::DAY_SHORT . ". " . Zend_Date::MONTH_NAME . " " . Zend_Date::YEAR;
    }

    public function detailAction()
    {
        $this->enableLayout();

    }


    private function getTown($town) {
        if ($town && $town != "Kleinwalsertal") {
            $towns = new Object_DemiTown_List();
            $towns->addConditionParam('name LIKE "%' . $town . '%"');
            $towns->setLimit(1);
            return $towns->load();
        } else {
            return null;
        }
    }
    private function getCategory($category) {

//        if (is_array($category)) {
//            $ids = [];
//            foreach ($category as $c) {
//                if ($c == "keine bevorzugte Unterkunftsart") {
//                    return null;
//                }
//                $categories = new Object_DemiCategory_List();
//                $categories->addConditionParam('name LIKE "%' . $c . '%"');
//                $categories->setOrderKey('order');
//                $categories->setOrder("ASC");
//                $categories->setLimit(1);
//                $ids[] = $categories->load()[0];
//            }
//            return $ids;
//
//        } else {
        if (!$category || $category == "keine bevorzugte Unterkunftsart") {
            return null;
        }
        $categories = new Object_DemiCategory_List();
        $categories->addConditionParam('name LIKE "%' . $category . '%"'); // hier vl auf = umstellen
        $categories->setOrderKey('order');
        $categories->setOrder("ASC");
        $categories->setLimit(1);
        return $categories->load();
//        }
    }

    private function getStars($param) {
        // mindestens 3 sterne / maximal 3 sterne auch noch  behandeln
        if ($param && $param != "keine") {
            $stars = new Object_DemiFilterObject_List();
            $stars->addConditionParam('name LIKE "%' . $param . '%"');
            $stars->setLimit(1);
            return $stars->load();
        } else {
            return null;
        }
    }
    private function getMealType($param) {
        if ($param && $param != "egal") {
            $mealType = new Object_DemiMealType_List();
            $mealType->addConditionParam('text LIKE "%' . $param . '%"');
            $mealType->setOrderKey('order');
            $mealType->setOrder("ASC");
            $mealType->setLimit(1);
            return $mealType->load();
        } else {
            return null;
        }
    }

    private function getHolidayThemes($param) {
        if ($param && $param != "egal") {
            $theme = new Object_DemiHolidayTheme_List();
            $theme->addConditionParam('name LIKE "%' . $param . '%" AND active = 1');
            $theme->setOrderKey('order');
            $theme->setOrder("ASC");
            $theme->setLimit(1);
            return $theme->load();
        } else {
            return null;
        }

    }
    private function getPeriod($param) {
        if ($param && $param != "") {
            return str_replace(" Nächte", "",$param);
        } else {
            return null;
        }

    }
    private function getAdults($param) {
        if ($param && $param != "") {
            switch ($param) {
                case "eine Person":
                    $adults = 1;
                    break;
                case "zwei Personen";
                    $adults = 2;
                    break;
                default:
                    $adults = 2;
            }
            return $adults;
        } else {
            return 2;
        }

    }


    private function startBooking($parameters, $sorting = null) {
        $startDate = new \Zend_Date($parameters['startDate']);
        $startDate = $startDate->get('dd.MM.YYYY');
        $endDate = new \Zend_Date($parameters['endDate']);
        $endDate = $endDate->get('dd.MM.YYYY');
        $town = $this->getTown($parameters["demi_ort"]);
        $category = $this->getCategory($parameters["unterkunftsart"]);
        $stars = $this->getStars($parameters["demi_stars"]);
        $board = $this->getMealType($parameters["board"]);
        $holidayThemes = $this->getHolidayThemes($parameters["interests"]);
        $period = $this->getPeriod($parameters['period']);
        $adults = $this->getAdults($parameters['adults']);

        if ($town) {
            $townId = $town[0]->getId();
        } else {
            $townId = "";
        }
        if ($category) {
            $categoryId = $category[0]->getId();
        } else {
            $categoryId = "";
        }
        if ($stars) {
            $starsId = $stars[0]->getId();
        } else {
            $starsId = null;
        }
        if ($board) {
            $boardId = $board[0]->getId();
        } else {
            $boardId = null;
        }
        if ($holidayThemes) {
            $holidayThemeId = $holidayThemes[0]->getId();
        } else {
            $holidayThemeId = null;
        }

//        if ($category) {
//            if (count($category) > 1) {
//                foreach($category as $c) {
//                    $categoryQuery[] = 'categories LIKE "%,' . $c->getId() . ',%"';
//                }
////                $list->addConditionParam('(' . implode("OR", $categoryQuery) . ')');
//
//            } else {
////                $list->addConditionParam('categories LIKE "%,' . $category[0]->getId() . ',%"');
//            }
//        }



        // hier bastel ich mir dann  meinen link zusammen, um die abfrage zu machen -> es soll json zurückkommen, das schick ich dann in die print acco!

        $url = "https://www.kleinwalsertal.com/cdemi?from=" . $startDate . "&to=" . $endDate . "&u0=1&a0=" . $adults . "&c0=0";
        if ($categoryId) {
            $url .= "&categories[]=" . $categoryId;
        }
        if ($townId) {
            $url .= "&towns[]=" . $townId;
        }
        if ($starsId) {
            $url .= "&fo_stars[]=" . $starsId;
        }
        if ($boardId) {
            $url .= "&mealtypes[]=" . $boardId;
        }
        if ($holidayThemeId) {
            $url .= "&holidaythemes[]=" . $holidayThemeId;
        }
        if ($period) {
            $url .= "&nights=" . $period;
        }
        if ($sorting == "price") {
            $url .= "&sorting=price";
        }

        $data = \Pimcore\Tool::getHttpData($url,[],['probe' => 1]);

        if ($data) {
            $dataArray = json_decode($data,true);
            foreach ($dataArray as $item) {
                if(is_null($item)) {
                    return null;
                } else {
                    return $dataArray;
                }
            }
        } else {
            return null;
        }

    }

    private function morePictures($params) {
        $list = $this->startBooking($params);
        $acco = $list[0];

        $dateFrom  = new Zend_Date($params['startDate'], "yyyy-MM-dd");
        $images = $acco->getImageDocuments(array(Deskline_Object_Adapter_AccommodationServiceProvider::DOCUMENT_TYPE_SERVICE_PROVIDER,Deskline_Object_Adapter_AccommodationServiceProvider::DOCUMENT_TYPE_SERVICE_PROVIDER_LOGO), true, $dateFrom);

        $thumbnails = [];
        foreach ($images as $imageDoc){
            $image = $imageDoc->getDocument();
            if ($image instanceof Asset_Image) {
                if ($image->getFormat() == "portrait") {
                    $thumbnail = 'demi_responsive_detail_big';
                } else {
                    $thumbnail = 'demi_gallery_detail_landscape';
                }


                $thumbnails[] = [
                    "link" => "https://www.kleinwalsertal.com" . $image->getThumbnail($thumbnail)->getPath(),
                    "title" => $title = htmlentities($imageDoc->getName())
                ];
            }
        }
        return $thumbnails;
    }

    private function printAcco($accos) {
        $attachments = array();
        $i = 0;
        $color = [ "#87B109", "#ffda29", "#2D9EE0"];
        foreach ($accos as $acco) {
            $attachment = [
                "id" => $acco['acco_id'],
                "color" => $color[$i],
                "title" => $acco['acco_headline'],
                "type" => $acco['acco_type'],
                "address" => $acco['acco_town'],
                "classification" => $acco['acco_stars'],
                "description" => $acco['acco_message'] . " " . $acco['acco_message_sub'],
                "board" => $acco['acco_meal'],
                "image_url" => $acco['acco_thumb'],
                "detail_url" => $acco['acco_detail_url']
            ];
            $attachments[] = $attachment;
            $i++;
        }
        return $attachments;
    }

    private function processMessage($update) {
        $parameters = $update["result"]["parameters"];
        $action = $update["result"]["action"];
        if ($action == "more.pictures") {
            $pictures = $this->morePictures($parameters);
            $this->sendMessage(array(
                "data" => [
                    "text" => "Hier sind die gewünschten Bilder:",
                    "attachments" => $pictures,

                ],
                "speech" => "morepictures"
            ));
        }
        if ($action == "booking.flexible") {
            $this->sendMessage(array(
                "data" => [
                    "text" => "Reisedauer",
                    "attachments" => null,

                ],
                "speech" => "nightsperiod"
            ));

        }
        if ($action == "cheapest.flexible.no" || $action == "cheapest.flexible.yes") {
            $list = $this->startBooking($parameters,"price");
            if ($list == null) {
                $this->sendMessage(array(
                    "data" => [
                        "text" => "Es gibt leider keine Hotels, die deinen Kriterien entsprechen.",
                        "attachments" => null,

                    ],
                    "speech" => "nohotels"
                ));
            } else {
                $attachments = $this->printAcco($list);
                if ($parameters['cheapest'] == "1") {
                    $cheapest = [];
                    $cheapest[] = $attachments[0];
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "Hier ist die günstigste Unterkunft, die auf deine Wünsche zutrifft:",
                            "attachments" => $cheapest,

                        ],
                        "speech" => "hotellist"
                    ));
                } else {
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "Hier sind die günstigsten Unterkunfte, die auf deine Wünsche zutriffen:",
                            "attachments" => $attachments,

                        ],
                        "speech" => "hotellist"
                    ));
                }


            }
        }
        if ($action != "booking.flexible" && $action != "more.pictures" && $action != "cheapest.flexible.no" && $action != "cheapest.flexible.yes") {
            $list = $this->startBooking($parameters);
            if ($list == null) {
                $this->sendMessage(array(
                    "data" => [
                        "text" => "Es gibt leider keine Hotels, die deinen Kriterien entsprechen.",
                        "attachments" => null,

                    ],
                    "speech" => "nohotels"
                ));
            } else {
                if (count($list) > 5) {
                    if ($action == "stars.unterkunft") {
                        $this->sendMessage(array(
                            "data" => [
                                "text" => "Es gibt zu viel Hotels",
                                "attachments" => null,

                            ],
                            "speech" => "toomanyhotelsafterstars"
                        ));
                    } else if ($action == "interests") {
                        $attachments = $this->printAcco($list);
                        $this->sendMessage(array(
                            "data" => [
                                "text" => "Hier habe ich einige Unterkünfte nach deinen Wünschen für dich zusammengestellt:",
                                "attachments" => $attachments,

                            ],
                            "speech" => "hotellist"
                        ));
                    } else if ($action == "board") {
                        $this->sendMessage(array(
                            "data" => [
                                "text" => "Es gibt zu viel Hotels",
                                "attachments" => null,

                            ],
                            "speech" => "toomanyhotelsafterboard"
                        ));


                    } else {
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
                            "text" => "Hier habe ich einige Unterkünfte für dich zusammengestellt:",
                            "attachments" => $attachments,

                        ],
                        "speech" => "hotellist"
                    ));
                }
            }
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
