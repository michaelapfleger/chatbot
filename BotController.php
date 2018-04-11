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
        $this->getResponse()->setHeader("X-Robots-Tag", "noindex, nofollow", true);
        $this->enableLayout();

    }
    public function probeAction()
    {
        $this->getResponse()->setHeader("X-Robots-Tag", "noindex, nofollow", true);
        $this->enableLayout();

    }

    private function getDomain() {
        $whatINeed = explode('/', $_SERVER['REQUEST_URI']);
        return $whatINeed[0];
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

        if (!$category || $category == "keine bevorzugte Unterkunftsart") {
            return null;
        }
        $categories = new Object_DemiCategory_List();
        $categories->addConditionParam('name LIKE "%' . $category . '%"'); // hier vl auf = umstellen
        $categories->setOrderKey('order');
        $categories->setOrder("ASC");
        $categories->setLimit(1);
        return $categories->load();
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
    private function getKids($param) {
        if ($param && $param != "") {
            preg_match_all('!\d+!', $param, $matches);
            if($matches) {
                return $matches[0];
            }
        } else {
            return 0;
        }
    }

    private function getSorting($param) {
        if ($param && $param != "") {
            switch ($param) {
                case "Preis aufsteigend":
                    $sorting = "price";
                    break;
                case "Sterne aufsteigend";
                    $sorting = "starsAsc";
                    break;
                case "Sterne absteigend";
                    $sorting = "stars";
                    break;
                case "Bewertungen absteigend";
                    $sorting = "ratingAverage";
                    break;
                case "Bewertungen aufsteigend";
                    $sorting = "ratingAverageAsc";
                    break;
                default:
                    $sorting = null;
            }
            return $sorting;
        } else {
            return null;
        }
    }

    private function getFacilities($params) {
        if ($params && $params != null && $params != []) {
            $facilities = new Object_DemiFacility_List();
            $facilities->addConditionParam('name LIKE "%' . $params . '%" AND active = 1');
            $facilities->setLimit(1);
            return $facilities->load();
        } else {
            return null;
        }
    }

    private function startBooking($parameters, $sorting = null, $showSelection = false) {
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
        $sortingType = $this->getSorting($parameters['sorting']);
        $facilities = $this->getFacilities($parameters['facilities']);
        $kids = $this->getKids($parameters['kids']);

        $selection = [];

        if ($category) {
            $categoryId = $category[0]->getId();
            $selection[] = "Deine aktuellen Kriterien: " . $parameters["unterkunftsart"];
        } else {
            $categoryId = "";
            $selection[] = "Deine aktuellen Kriterien: Unterkunft";
        }

        if ($town) {
            $townId = $town[0]->getId();
            $selection[] = "in " . $parameters["demi_ort"];
        } else {
            $townId = "";
        }
        if ($kids) {
            $selection[] = $parameters['kids'];
        }
        $selection[] = $parameters['adults'];
        $selection[] = "Anreisedatum: " . $startDate;
        $selection[] = "Abreisedatum: " . $endDate;
        if ($period) {
            $selection[] = "flexibel";
            $selection[] = $period . " Nächte";
        }

        if ($stars) {
            $starsId = $stars[0]->getId();
            $selection[] = $stars;
        } else {
            $starsId = null;
        }
        if ($board) {
            $boardId = $board[0]->getId();
            $selection[] = $parameters["board"];
        } else {
            $boardId = null;
        }
        if ($holidayThemes) {
            $holidayThemeId = $holidayThemes[0]->getId();
            $selection[] = $holidayThemes;
        } else {
            $holidayThemeId = null;
        }
        if ($facilities) {
            $facilityId = $facilities[0]->getId();
            $selection[] = $parameters['facilities'];
        } else {
            $facilityId = null;
        }

        if ($showSelection) {
            return implode(", ", $selection);
        }
        $url = "https://www.kleinwalsertal.com/cdemi?from=" . $startDate . "&to=" . $endDate . "&u0=1&a0=" . $adults . "&c0=" . $kids;
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
        if ($sortingType) {
            $url .= "&sorting=" . $sortingType;
        }
        if ($facilityId) {
            $url .= "&facilities[]=" . $facilityId;
        }

        $data = \Pimcore\Tool::getHttpData($url,[],['probe' => 1]);

        if ($data) {
            $dataArray = json_decode($data,true);
            foreach ($dataArray as $item) {
                if(is_null($item)) {
                    return [
                        'data' => null,
                        'text' => implode(", ", $selection),
                        'url'  => $url
                    ];
                } else {
                    return [
                        'data' => $dataArray,
                        'text' => implode(", ", $selection),
                        'url'  => $url
                    ];
                }
            }
        } else {
            return [
                'data' => null,
                'text' => implode(", ", $selection),
                'url'  => $url
            ];
        }
    }

    private function substitutes($parameters, $sorting = null) {
        $newParams = $parameters;
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
        $sortingType = $this->getSorting($parameters['sorting']);
        $facilities = $this->getFacilities($parameters['facilities']);
        $kids = $this->getKids($parameters['kids']);

        $selection = [];
        if ($category && $board) {
            if ($facilities){
                $facilities = null;
                $newParams['facilities'] = "";
            } else {
                $categoryId = $category[0]->getId();
                $selection[] = $parameters["unterkunftsart"];
                $newParams['board'] = "";
                $boardId = null;
            }
        } else {
            if ($board) {
                if ($facilities) {
                    $facilities = null;
                    $boardId = $board[0]->getId();
                    $selection[] = $parameters["board"];
                    $newParams['facilities'] = "";
                }  else {
                    $boardId = null;
                    $newParams['board'] = "";
                }
            }
            if ($category) {
                $categoryId = $category[0]->getId();
                $selection[] = $parameters["unterkunftsart"];
            } else {
                $categoryId = "";
                $selection[] = "Unterkunft";
            }
        }

        if ($town) {
            $townId = $town[0]->getId();
            $selection[] = "in " . $parameters["demi_ort"];
        } else {
            $townId = "";
        }

        if ($kids) {
            $selection[] = $parameters['kids'];
        }
        $selection[] = $parameters['adults'];
        $selection[] = "Anreisedatum: " . $startDate;
        $selection[] = "Abreisedatum: " . $endDate;
        if ($period) {
            $selection[] = "flexibel";
            $selection[] = $period . " Nächte";
        }

        if ($stars) {
            $starsId = $stars[0]->getId();
            $selection[] = $stars;
        } else {
            $starsId = null;
        }

        if ($holidayThemes) {
            $holidayThemeId = $holidayThemes[0]->getId();
            $selection[] = $holidayThemes;
        } else {
            $holidayThemeId = null;
        }
        if ($facilities) {
            $facilityId = $facilities[0]->getId();
            $selection[] = $parameters['facilities'];
        } else {
            $facilityId = null;
        }

        $url = "https://www.kleinwalsertal.com/cdemi?from=" . $startDate . "&to=" . $endDate . "&u0=1&a0=" . $adults . "&c0=" . $kids;
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
        if ($sortingType) {
            $url .= "&sorting=" . $sortingType;
        }
        if ($facilityId) {
            $url .= "&facilities[]=" . $facilityId;
        }


        $data = \Pimcore\Tool::getHttpData($url,[],['probe' => 1]);

        if ($data) {
            $dataArray = json_decode($data,true);
            foreach ($dataArray as $item) {
                if(is_null($item)) {
                    return [
                        'data' => null,
                        'text' => implode(", ", $selection),
                        'url'  => $url,
                        'params'  => null
                    ];
                } else {
                    return [
                        'data' => $dataArray,
                        'text' => implode(", ", $selection),
                        'params' => $newParams,
                        'url'  => $url
                    ];
                }
            }
        } else {
            return [
                'data' => null,
                'text' => implode(", ", $selection),
                'url'  => $url,
                'params'  => null
            ];
        }
    }

    private function showPictures($params) {
        $accoId = $params['accoId'];
        if ($accoId) {
            /** @var Demi_AccommodationServiceProvider $acco */
            $acco = Demi_AccommodationServiceProvider::getById($accoId);
            if ($acco) {

                $dateFrom = new Zend_Date($params['startDate'], "yyyy-MM-dd");
                $images = $acco->getImageDocuments(array(Deskline_Object_Adapter_AccommodationServiceProvider::DOCUMENT_TYPE_SERVICE_PROVIDER, Deskline_Object_Adapter_AccommodationServiceProvider::DOCUMENT_TYPE_SERVICE_PROVIDER_LOGO), true, $dateFrom);

                $thumbnails = [];
                foreach ($images as $imageDoc) {
                    $image = $imageDoc->getDocument();
                    if ($image instanceof Asset_Image) {
                        if ($image->getFormat() == "portrait") {
                            $thumbnail = 'demi_responsive_detail_big';
                        } else {
                            $thumbnail = 'demi_gallery_detail_landscape';
                        }


                        $thumbnails[] = [
                            "link" => $this->getDomain() . $image->getThumbnail($thumbnail)->getPath(),
                            "title" => $title = htmlentities($imageDoc->getName())
                        ];
                    }
                }
                return $thumbnails;
            }
        }
        return null;
    }

    private function showContact($params) {
        $accoId = $params['accoId'];
        if ($accoId) {
            /** @var Demi_AccommodationServiceProvider $acco */
            $acco = Demi_AccommodationServiceProvider::getById($accoId);
            if ($acco) {
                $address = $acco->getAddress();
                if ($address) {
                    $phone = $address->getPhone();
                    $email = $address->getEmail();
                    $url = $address->getUrl() && Demi2015_Website_Helper::findNiceUrl($address->getUrl()) ? Demi2015_Website_Helper::findNiceUrl($address->getUrl()) : "";
                    return [
                        "phone" => $phone,
                        "email" => $email,
                        "url" => $url
                    ];
                }
            }
        }

        return null;
    }
    private function showRating($params) {
        $accoId = $params['accoId'];
        if ($accoId) {
            /** @var Demi_AccommodationServiceProvider $acco */
            $acco = Demi_AccommodationServiceProvider::getById($accoId);
            if ($acco) {
                if ($acco->getRatingCode() && $acco->getRatingCount() > 0) {
                    $totalRating = $acco->getRatingCount() . " " . $this->view->translate("demi.Bewertungen");
                    $rating = $acco->getRatingAverage();
                    return [
                        'rating' => $rating,
                        'totalRating' => $totalRating,
                        'widget' => "https://api.trustyou.com/hotels/" . $acco->getRatingCode() ."/meta_review.html?lang=de"
                    ];
                }
            }
        }

        return null;
    }

    private function showEvents($params) {
        $startDate = $params['startDate'];
        $endDate = $params['endDate'];
        $domain = $this->getDomain();
        $events = $this->view->getProperty('eventOverview')->getFullPath();
        $url = $domain . $events . "?category=0&keyword=&from=" . $startDate . "&to=" . $endDate;
        return $url;
    }
    private function showAddress($params) {
        $accoId = $params['accoId'];
        if ($accoId) {
            /** @var Demi_AccommodationServiceProvider $acco */
            $acco = Demi_AccommodationServiceProvider::getById($accoId);
            if ($acco) {
                $address = $acco->getAddress();
                $addressInfos = $address->getAddressLine1() . " " . $address->getAddressLine2() . " " . $address->getZipcode() . " " . $address->getTown() . " " . $address->getCity() . " " . $address->getCountry();
                $latlng = "";
                if($acco->getPosition()) {
                    $pos = $acco->getPosition();
                    if($pos->getLatitude() && $pos->getLongitude()) {
                        $lat = $pos->getLatitude();
                        $lng = $pos->getLongitude();
                        $latlng = $lat . ", " . $lng;
                    }
                }
                return [
                    "text" => $addressInfos,
                    "maps" => "http://maps.apple.com/?daddr=". $addressInfos . "+%40" . $latlng
                ];
            }
        }
        return null;
    }


    private function showFacilities($params, $type = null) {
        $accoId = $params['accoId'];
        if ($accoId) {
            /** @var Demi_AccommodationServiceProvider $acco */
            $acco = Demi_AccommodationServiceProvider::getById($accoId);
            if ($acco) {
                $facilities = $acco->getFacilities();
                $sortedFacilities = [];
                /** @var $facility Demi_Facility */
                if(!empty($facilities)) {
                    foreach($facilities as $facilityColl) {
                        $facility = $facilityColl->getFacility();
                        if($facility) {
                            $facGroup = $facility->getFacilityGroup();
                            if($facGroup) { //$facGroup
                                if(! isset($sortedFacilities[$facGroup->getId()])) {
                                    $sortedFacilities[$facGroup->getId()] = [
                                        "name" => $facGroup->getName(),
                                        "keyname" => $facGroup->getName("de"),
                                        "facilities" => []
                                    ];
                                }
                                $facName = $facility->getName();
                                if ($facility->getValueType() == "IntDigit") {
                                    $facName .= ": " . $facilityColl->getFacilityValue();
                                }
                                $sortedFacilities[$facGroup->getId()]["facilities"][] = $facName;
                            }
                        }

                    }
                }
                if ($type) {
                    foreach ($sortedFacilities as $facilityGroup) {
                        if ($facilityGroup['keyname'] == $type) {
                            return $facilityGroup;
                        }
                    }
                } else {
                    return $sortedFacilities;
                }

            }
        }
        return null;
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
                "detail_url" => $acco['acco_detail_url'],
                "price"     => $acco['acco_price'],
                "rating" => $acco['acco_rating'],
                "totalRating" => $acco['acco_total_rating']
            ];
            $attachments[] = $attachment;
            $i++;
        }
        return $attachments;
    }

    private function processMessage($update) {
        $parameters = $update["result"]["parameters"];
        $action = $update["result"]["action"];

        switch ($action) {
            case "show.facilities":
                $dataSport = $this->showFacilities($parameters, "Sport / Freizeit");
                $dataWellness = $this->showFacilities($parameters, "Wellness");
                $dataEinrichtung = $this->showFacilities($parameters, "Einrichtungen Betrieb");
                if ($dataSport || $dataWellness || $dataEinrichtung) {
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "'" . $parameters['accoName'] ."' bietet folgende Ausstattung:",
                            "sport" =>  $dataSport ? implode(", " ,$dataSport['facilities']) : "",
                            "wellness" =>  $dataWellness ? implode(", " ,$dataWellness['facilities']) : "",
                            "einrichtungen" => $dataEinrichtung ? implode(", " ,$dataEinrichtung['facilities']) : ""
                        ],
                        "speech" => "showfacilities"
                    ));
                } else {
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "Ich habe leider keine Informationen über die Ausstattung von '" . $parameters['accoName'],
                        ],
                        "speech" => "showfacilities"
                    ));
                }
                break;
            case "show.contact":
                $contact = $this->showContact($parameters);
                if ($contact) {
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "Hier sind die Kontaktdaten von '" . $parameters['accoName'] . "':",
                            "phone" => $contact['phone'],
                            "email" => $contact['email'],
                            "url" => $contact['url']
                        ],
                        "speech" => "showcontact"
                    ));
                } else {
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "Ich habe leider keine Kontaktdaten für '" . $parameters['accoName'] . "' gefunden."

                        ],
                        "speech" => "showcontact"
                    ));
                }
                break;
            case "show.criteria":
                $criteria = $this->startBooking($parameters, null, true);
                $this->sendMessage(array(
                    "data" => [
                        "text" => $criteria

                    ],
                    "speech" => "showcriteria"
                ));
                break;
            case "show.pictures":
                $pictures = $this->showPictures($parameters);
                $this->sendMessage(array(
                    "data" => [
                        "text" => "Hier sind die gewünschten Bilder:",
                        "attachments" => $pictures,

                    ],
                    "speech" => "showpictures"
                ));
                break;
            case "show.rating":
                $data = $this->showRating($parameters);
                if ($data) {
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "'" . $parameters['accoName'] ."' erhielt bisher " . $data['totalRating'] . " mit einer durchschnittlichen Bewertung von " . $data['rating'] . " von 5 Sternen.",
                            "widget" => $data["widget"]
                        ],
                        "speech" => "showrating"
                    ));
                } else {
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "'" . $parameters['accoName'] ."' erhielt bisher keine Bewertungen.",
                        ],
                        "speech" => "showrating"
                    ));
                }
                break;
            case "show.paymentmethods":
                $data = $this->showFacilities($parameters, "Zahlungsarten");
                if ($data) {
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "'" . $parameters['accoName'] ."' bietet folgende Zahlungsarten: " . implode(", " ,$data['facilities'])
                        ],
                        "speech" => "showpaymentmethods"
                    ));
                } else {
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "Ich habe leider keine Informationen von '" . $parameters['accoName'] ."' bezüglich möglicher Zahlungsarten.",
                        ],
                        "speech" => "showpaymentmethods"
                    ));
                }
                break;
            case "show.location":
                $address = $this->showAddress($parameters);
                $data = $this->showFacilities($parameters, "Lage");
                if ($data) {
                    $this->sendMessage(array(
                        "data" => [
                            "text" => implode(", " ,$data['facilities']),
                            "address" =>  $address ? "Die Adresse von '" . $parameters['accoName'] . "' lautet " . $address['text'] : "",
                            "maps" => $address ? $address['maps'] : ""
                        ],
                        "speech" => "showlocation"
                    ));
                } else {
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "Ich habe leider keine Informationen von '" . $parameters['accoName'] ."' bezüglich der Lage.",
                        ],
                        "speech" => "showlocation"
                    ));
                }
                break;
            case "booking.flexible":
                $this->sendMessage(array(
                    "data" => [
                        "text" => "Welche Reisedauer bevorzugst du?",
                        "attachments" => null,
                        "options"   => "1 - 4 Nächte, 3 - 6 Nächte, 5 - 8 Nächte, 7 - 10 Nächte, 10 - 14 Nächte"

                    ],
                    "speech" => "nightsperiod"
                ));
                break;
            case "show.events":
                $url = $this->showEvents($parameters);
                $this->sendMessage(array(
                    "data" => [
                        "text" => "Hier sind einige Veranstaltungen, die während deines Auftenthaltes stattfinden:",
                        "url" => $url

                    ],
                    "speech" => "showevents"
                ));
                break;

            case "cheapest.flexible.no":
            case "cheapest.flexible.yes":
                $result = $this->startBooking($parameters,"price");
                $list = $result['data'];
                $text = $result['text'];
                if ($list == null) {
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "Es gibt leider keine Hotels, die deinen Kriterien entsprechen.",
                            "attachments" => null,
                            "selection" => $text

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
                                "selection" => $text

                            ],
                            "speech" => "hotellist"
                        ));
                    } else {
                        $this->sendMessage(array(
                            "data" => [
                                "text" => "Hier sind die günstigsten Unterkunfte, die auf deine Wünsche zutriffen:",
                                "attachments" => $attachments,
                                "selection" => $text

                            ],
                            "speech" => "hotellist"
                        ));
                    }


                }
                break;

            default:
                $result = $this->startBooking($parameters);
                $list = $result['data'];
                $text = $result['text'];
                $url = $result['url'];


                if ($list == null) {
                    $newResult = $this->substitutes($parameters);
                    $newList = $newResult['data'];
                    $newText = $newResult['text'];
                    $newParams = $newResult['params'];
                    $newUrl = $newResult['url'];
                    $this->sendMessage(array(
                        "data" => [
                            "text" => "Es gibt leider keine Unterkünfte, die deinen Kriterien entsprechen.",
                            "selection" => $text,
                            "url"       => $url,
                            "newText" => "Aber ich habe " . count($newList) > 1 ? count($newList) . " Ergebnisse" : count($newList) . " Ergebnis" . "  zu folgenden Kriterien: " . $newText,
                            "newSelection" => "Ich kann sie dir anzeigen oder du änderst deine Suchanfrage, indem du ein oder mehrere Kriterien abwandelst.",
                            "attachments" => $newList,
                            "newParams" => $newParams,
                            "newUrl" => $newUrl

                        ],
                        "speech" => "nohotels"
                    ));
                } else {
                    if (count($list) > 5 && count($list) < 20 && $action != "display.results") {
                        $this->sendMessage(array(
                            "data" => [
                                "text" => "Ich habe " . count($list) . " Unterkünfte für dich gefunden. Ich kann sie dir anzeigen oder du gibst ein weiteres Suchkriterium ein.",
                                "attachments" => null,
                                "selection" => $text

                            ],
                            "speech" => "enoughhotels"
                        ));
                    } else if (count($list) > 20 && $action != "display.results") {
                        $this->sendMessage(array(
                            "data" => [
                                "text" => "Ich habe " . count($list) . " Unterkünfte für dich gefunden. Bitte gib weitere Suchkriterien ein, um die Auswahl weiter einzuschränken.",
                                "attachments" => null,
                                "selection" => $text

                            ],
                            "speech" => "toomanyhotels"
                        ));
                    } else {
                        $attachments = $this->printAcco($list);
                        $this->sendMessage(array(
                            "data" => [
                                "text" => "Folgende Unterkünfte treffen auf deine Kriterien zu. Wenn du eine Unterkunft auswählst, kannst du mir weitere Fragen dazu stellen.",
                                "attachments" => $attachments,
                                "selection" => $text

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
