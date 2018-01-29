<?php

function getJsonResults($acco,$product,$ecImpressionObject,$isPackageSearch,$resultSet,$unitCategoryArray,$minPrice,$detailUrl,$cleanDetailUrl,$noDate,$i,$listPos,$view) {
    $trackingService = $view->trackingService;

    /** @var Demi_AccommodationServiceProvider $acco */
//    $acco = $this->getParam('acco');
    $specialsActivated = false;
    $mGroups = $acco->getMarketingGroups();
    $mgImages = array();
    /** @var Demi_MarketingGroup $mGroup */
    foreach($mGroups as $mGroup) {
        //$img = $mGroup->getIconSmall();
        $img = $mGroup->getIconLocalized();
        if($img instanceof Asset_Image) {
            $tmpImg = array(
                "sort" => $mGroup->getIconSort(),
                "img" => $img->getThumbnail("demi_list_mg_icon"),
                "desc" => $mGroup->getName(),
                "tooltip" => $mGroup->getTooltip(),
            );
            $mgImages[] = $tmpImg;
        }
    }
    if(!empty($mgImages)) {
        usort($mgImages, function ($a, $b) {
            $sortA = (int)$a["sort"];
            $sortB = (int)$b["sort"];
            if ($sortA == $sortB) {
                return 0;
            } else if ($sortA < $sortB) {
                return 1;
            } else {
                return -1;
            }
        });
    }

    /** @var Demi_Accommodation_Search_ResultSet_Accommodation $resultSet */
//    $resultSet = $this->getParam('resultSet');
//    $ecImpressionObject = $this->getParam('ecImpressionObject');
//    $minPrice = $this->getParam('minPrice');
    $minPriceProducts = array();
    if($resultSet instanceof Demi_Accommodation_Search_ResultSet_Accommodation) {
        $minPriceProducts = $resultSet->getMinPriceProductSets();
    }
    $isBestPrice = !empty($minPriceProducts);

    /** @var Demi_Accommodation_Search_ResultSet_Product $minPriceProduct */
    foreach($minPriceProducts as $minPriceProduct) {
        $isBestPrice = $isBestPrice && $minPriceProduct->getIsBestPrice();
    }

//    $unitCategoryArray = $this->getParam('unitCategoryArray');
//    $detailUrl = $this->getParam('detailUrl');
//    $cleanDetailUrl = $this->getParam('cleanDetailUrl');
//    $noDate = $this->getParam('noDate');

//    $isPackageSearch = $this->getParam('isPackageSearch');

    /** @var Demi_Accommodation_Search_Parameter $params */
    $params = $view->searchParams;
    $dateFrom = $params->getDateFrom();
    $zendDateFormat = Zend_Date::DAY . "." . Zend_Date::MONTH . "." . Zend_Date::YEAR;
    if( $dateFrom ){
        $zendDateFrom = new Zend_Date($dateFrom->format("d.m.Y"), $zendDateFormat);
    }

    $maxVacanciesForWarning = 5;

    $ratingAverageMethod = $view->ratingAverageMethod;
    $ratingCountMethod = $view->ratingCountMethod;
    $ratingMaxCount = $view->ratingMaxCount;
//    $i = $this->getParam('i');

    if($resultSet instanceof Demi_Accommodation_Search_ResultSet_Accommodation) {
        $specials = $resultSet->getAllSpecials();
        $hasSpecials = $resultSet->hasSpecials();
    }

    $bestPrice = false;

    $oldPrice = 0;
    $minPriceSpecials = array();

    /** @var Demi_Accommodation_Search_ResultSet_Product $minPriceProduct */
    foreach($minPriceProducts as $minPriceProduct) {

        $tmpPriceData = $minPriceProduct->getPriceData();
        if($tmpPriceData && $tmpPriceData->getPriceBeforeSpecial() > $minPriceProduct->getPrice()) {
            $oldPrice =+ $tmpPriceData->getPriceBeforeSpecial();
            if($tmpPriceData->getSplitPay() && $tmpPriceData->getSplitStay()) {
                $minPriceSpecials[] = $view->translate("demi.stay") . " " . $tmpPriceData->getSplitStay() . " " . $view->translate("demi.pay") . " " . $tmpPriceData->getSplitPay();
            }
            if($tmpPriceData->getSpecialPriceName()) {
                $tmpTranslation = $view->translate(Demi_Accommodation_Search_ResultSet_Product_PriceData::DEMI_SPECIAL_PRICE_TRANSLATION_PREFIX . $tmpPriceData->getSpecialPriceName());
                if($tmpTranslation == (Demi_Accommodation_Search_ResultSet_Product_PriceData::DEMI_SPECIAL_PRICE_TRANSLATION_PREFIX . $tmpPriceData->getSpecialPriceName())) {
                    $tmpTranslation = $tmpPriceData->getSpecialPriceName();
                }
                $minPriceSpecials[] = $tmpTranslation;
            }
        }
    }

    if($isPackageSearch and $resultSet instanceof Demi_Accommodation_Search_ResultSet_HousePackageMaster) {
        $housePackageMaster = Demi_HousePackageMaster::getById($resultSet->getHousePackageMasterId());
    } else if ($isPackageSearch) {
        /** @var Demi_AccommodationProduct $product */
        $product = $product;
        /** @var Demi_AccommodationService $product */
        $service = $product->getService();
        $housePackageMaster = $product->getPackageMaster();
    }
    if($resultSet instanceof Demi_Accommodation_Search_ResultSet_Accommodation && $resultSet->getIsTop()) {
        $top = "top-hotel";
    } else {
        $top = "";
    }

    if($acco) {
        $gaBookableStr = '';
        if($isPackageSearch and !$resultSet instanceof Demi_Accommodation_Search_ResultSet_HousePackageMaster) {
            $gaBookableStr = ($resultSet->getBookable() || $resultSet->getBookableOnRequest())?'data-demi-ga-bookable="true"':'';
        } else {
            $gaBookableStr = $resultSet->getActualBookable()?'data-demi-ga-bookable="true"':'';
        }
        $gaObj = $trackingService->getCompleteImpressionObject($acco, $params, $listPos);
        $view->gaObjs[] = $gaObj;

        $mg = [];
        $marketingGroups = $acco->getMarketingGroups();
        foreach($marketingGroups as $marketingGroup) {
            if($marketingGroup->getFid() == "85392dfc-f8dc-4aa7-8c85-55b9ba91817b" || $marketingGroup->getFid() == "ca5d024e-d0d3-457e-8c21-7327fde12890"
                || $marketingGroup->getFid() == "58e7b7e0-2b4a-4d1c-b9ee-3b57d173dbe7") {
                $mg[$marketingGroup->getFid()] = $marketingGroup;
            }
        }

        ?>

        <?php


        if($isPackageSearch) {
            $images = null;
            if($housePackageMaster){
                $images = $housePackageMaster->getImages();
                $image = $images[0];
            }
            if(empty($images) and $product) {
                $images = $product->getDocuments();
                if(empty($images) and $service) {
                    $image = $service->getFirstImage(null, $dateFrom);
                }
                else {
                    $image = $images[0]->getDocument();
                }
            }
        }
        if(!$image) {
            $image = $acco->getFirstImage(null, $dateFrom);
        }
        if($image instanceof Asset_Image) {
            $acco_thumb = "https://www.kleinwalsertal.com" . $image->getThumbnail("demi_responsive_list")->getPath();
        } else {
            $acco_thumb = "https://www.kleinwalsertal.com" . $view->document->getProperty('demi_hotel_fallback_image');
        }

        if($isPackageSearch) {
            if($housePackageMaster){
                $headline = $housePackageMaster->getName();
                $subline = str_replace("*", "", $acco->getName());
            } else {
                $headline =  $product->getName();
                $subline = str_replace("*", "", $acco->getName());
            }
        } else {
            $headline = preg_replace('/(\*+)(s|S{0,1})/', "", $acco->getName());
            $subline = "";
        }
        $stars = $acco->getStars();
        if ($stars instanceof Demi_Stars) {
            $starNumbers = $stars->getStarsNumbers();
            if($stars->getIsSuperior()) {
                $starNumbers .= "S";
            }
        } else {
            $starNumbers = "";
        }

        $accoClassification = "";
        foreach($acco->getClassifications() as $classification) { ?>
            <?php $count = $classification->getClassificationGroupAmount();
            $group = $classification->getClassificationGroup();
            if($group) {
                $groupName = $group->getName("en");
                if(strpos($groupName, " ") !== false) {
                    $groupName = explode(" ", $groupName)[0];
                }
                for($c=0; $c < $count; $c++) {
                    $accoClassification .= strtolower($groupName);
                } ?>
            <?php } ?>
        <?php } ?>

        <?php
        $town = $acco->getTown();
        $townstr = "";
        $townstrArray = array();
        ?>
        <?php if($town) {
            $townstrArray[] = $town->getName();
            $townstr = implode(", ", $townstrArray);
            $accoTown = $townstr;
        } ?>
        <?php if ($acco->getPosition() instanceof Object_Data_Geopoint) {
            if($acco->getPosition()->getLongitude() && $acco->getPosition()->getLatitude()) {
                $accoLong = $acco->getPosition()->getLongitude();
                $accoLat = $acco->getPosition()->getLatitude();
            } ?>
        <?php } ?>

        <?php if(!$noDate) {
            //use array for multiple rows
            $totalVacanciesArray = array();
            $totalOffers = array();
            if($isPackageSearch and !$resultSet instanceof Demi_Accommodation_Search_ResultSet_HousePackageMaster) {
                $totalVacancies = $resultSet->getUnits();
                $actualBookable = $resultSet->getBookable() || $resultSet->getBookableOnRequest();
            } else {
                foreach ($resultSet->getRoomRows() as $roomrow) {
                    foreach ($roomrow->getProducts() as $product) {
                        $useId = $product->getServiceId();
                        if($product->getAvailabilityReference() == "product") {
                            $useId = $product->getProductId();
                        } else if($product->getAvailabilityReference() == "gap") {
                            $useId = $product->getGapId();
                        }
                        // always take highest value. When searching with 2 roomrows, there is the possibility that the Units
                        // of one product in one row is reduced due to the need to use it in another specific row (ie. if no other
                        // fitting Product is available for that row)
                        if(!isset($totalVacanciesArray[$useId]) || $totalVacanciesArray[$useId] < $product->getUnits()) {
                            $totalVacanciesArray[$useId] = $product->getUnits();
                        }
                        $totalOffers[$product->getProductId()] = true;
                    }
                }
                $totalVacancies = 0;
                foreach ($totalVacanciesArray as $unitcount) {
                    $totalVacancies = $totalVacancies + $unitcount;
                }
                $actualBookable = $resultSet->getActualBookable();
            }
            // TODO Won't work
            $roomStr = $view->translate("demi.Zimmer");
            if($totalVacancies > 1) {
                $roomStr = $view->translate("demi.Zimmer");
            }

            $roomStr .= " " . $view->translate("demi.frei");

            $categories = $acco->getCategories();
            foreach($categories as $cat) {
                if(in_array($cat->getFid(), $unitCategoryArray)) {
                    if($totalVacancies > 1) {
                        $roomStr = $view->translate("demi.Einheiten frei");
                    } else {
                        $roomStr = $view->translate("demi.Einheit frei");
                    }
                }
            }


            ?>
            <?php if($actualBookable) { ?>
                <?php $acco_message = $view->translate("demi.Noch") . " " . $totalVacancies . " "  . $roomStr; ?>
                <?php if($totalVacancies < $maxVacanciesForWarning) { ?>
                    <?php $acco_message_sub = $view->translate("demi.Letzte Chance, Jetzt zugreifen!"); ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>

        <?php
        $acco_price = "";
        $acco_meal = "";
        if($noDate and $resultSet instanceof Demi_Accommodation_Search_ResultSet_HousePackageMaster) {
            $myMinPrice = 0;
            $myPriceInfo = null;
            foreach($resultSet->getProducts() as $product){
                if($myMinPrice==0 or $myMinPrice>$product->getBasePrice()){
                    $myMinPrice=$product->getBasePrice();
                    $priceInfo = $product->getPriceInfo();
                }
            }

            $minPrice = $myMinPrice;
        } else {
            $priceInfo = $resultSet->getPriceInfo();
        }

        ?>
        <?php if($minPrice > 0) { ?>
            <?php if($view->isCorridor || $params->isNoDate()) { ?>
                <?php $acco_price .= $view->translate('demi.Ab'); ?>
            <?php } else { ?>
                <?php $acco_price .= $view->translate('demi.Für alle Gäste ab'); ?>
            <?php } ?>

            <?php if($oldPrice > $minPrice && $specialsActivated) { ?>
                <?php $acco_price .= " " . $view->curr->toCurrency($oldPrice, array("precision" => 2)); ?>
            <?php } ?>

            <?php $acco_price .= " ".  $view->curr->toCurrency($minPrice, array("precision" => 2)); ?>

            <?php if($noDate) {
                if($priceInfo["nights"] > 0) {
                    $nightStr = $view->translate("demi.Nacht");
                    if($priceInfo["nights"] > 1) {
                        $nightStr = $priceInfo["nights"] . " " . $view->translate("demi.Nächte");
                    }
                    $nightStr = " / " . $nightStr;
                } ?>
                <?php if(!empty($priceInfo["type"])) { ?>
                    <?php $acco_price .= " " .  $view->translate("demi." . $priceInfo["type"]) . " "  .$nightStr; ?>
                <?php } ?>
            <?php } ?>
            <?php if($view->isCorridor) {
                $nightStr = $view->translate("demi.PerPerson") . " / " . $view->translate("demi.Nacht");
                ?>
                <?php $acco_price .= " " . $nightStr; ?>
            <?php } ?>
            <?php if($isPackageSearch and !$resultSet instanceof Demi_Accommodation_Search_ResultSet_HousePackageMaster) { ?>
                <?php $acco_meal =  $resultSet->getMealCode(); ?>
            <?php } else { ?>
                <?php $acco_meal = Demi2015_Website_Helper::getMealsFromMinPriceproducts($resultSet, true, $params); ?>
            <?php } ?>
        <?php } ?>


        <?php
        if($acco->getRatingCode() && $acco->$ratingAverageMethod() > 0) { ?>
            <?php $valueAsFiveStar = Demi2015_Website_Helper::getRatingValueAsFiveStarValue($acco->$ratingAverageMethod(),$ratingMaxCount);
            $totalRatings = $acco->$ratingCountMethod() . " " . $view->translate("demi.Bewertungen");
            $ratingImg = "";
            if($acco->getRatingSystem() == Demi_Rating_Const::TRUST_YOU) {
                $ratingImg = "https://www.kleinwalsertal.com/static/demi2015/img/tylogo.png";
            } else if($acco->getRatingSystem() == Demi_Rating_Const::HOTEL_NAVIGATOR) {
                $ratingImg = "https://www.kleinwalsertal.com/static/demi2015/img/hn-logo.gif";
            }
            ?>
        <?php } ?>

        <?php
        $acco_marketing = null;
        if($mg["85392dfc-f8dc-4aa7-8c85-55b9ba91817b"]) {
            $acco_marketing[] = "best_price";
        }
        if($mg["ca5d024e-d0d3-457e-8c21-7327fde12890"]) {
            $acco_marketing[] = "bergbahn";
        }
        if($mg["58e7b7e0-2b4a-4d1c-b9ee-3b57d173dbe7"]) {
            $acco_marketing[] = "skipass";
        }
        ?>

        <?php
        $hotelrow = [
            'acco_id'           => $acco->getId(),
            'acco_detail_url'   => "https://www.kleinwalsertal.com" . $detailUrl,
            'acco_thumb'        => $acco_thumb,
            'acco_marketing'    => $acco_marketing,
            'acco_headline'     => $headline,
            'acco_subline'      => $subline,
            'acco_classification'   => $accoClassification,
            'acco_stars'        => $starNumbers,
            'acco_type'         => $acco->getCategoryNames(2),
            'acco_town'         => $accoTown,
            'acco_lat'          => $accoLat,
            'acco_long'         => $accoLong,
            'acco_message'      => $acco_message,
            'acco_message_sub'  => $acco_message_sub,
            'acco_price'        => $acco_price,
            'acco_meal'         => $acco_meal,
            'acco_rating'       => $valueAsFiveStar,
            'acco_total_rating' => $totalRatings,
            'acco_rating_img'   => $ratingImg
        ];
        return $hotelrow;

    }
    return null;
}






$unitCategoryArray = Demi2015_Website_Helper::$unitCategoryArray;

/** @var \Demi2015\Website\Tracking\Service $trackingService */
$trackingService = $this->trackingService;

$maxVacanciesForWarning = 5;

/** @var $params Demi_Accommodation_Search_Parameter */
$params = $this->searchParams;
$isPackageSearch = $params->getSearchType() == Demi_Accommodation_Search_Parameter::SEARCH_TYPE_ONE_PRODUCT_PER_PACKAGE;
$page = $this->getParam("page");

if(!$page || $page <= 0) {
    $page = 1;
}

/** @var $paginator Zend_Paginator */
$paginator = $this->paginator;

$dateFormat = "%a %e. %b %Y";

if($params->getDateFrom()) {
    $dateFrom = new Zend_Date($params->getDateFrom()->getTimestamp());
} else {
    $dateFrom = new Zend_Date();
}

$noDate = false;
if(!$params->getDateFrom() || !$params->getDateTo()) {
    $noDate = true;
}

$sortingArray = $params->getOrderKey("sorting");
$sorting = "";
foreach($sortingArray as $sort) {
    if($sort != "Random" || empty($sorting)) {
        $sorting = $sort;
    }
}
$orderKey = $sorting;

$dc = $this->dc;
$ratingAverageMethod = "get" . ucfirst($dc->getRatingAverageColumn());
$ratingCountMethod = "get" . ucfirst($dc->getRatingCountColumn());
$ratingMaxCount = 100;
$this->ratingAverageMethod = "get" . ucfirst($dc->getRatingAverageColumn());
$this->ratingCountMethod = "get" . ucfirst($dc->getRatingCountColumn());
$this->ratingMaxCount = 100;



$gaCategory1stLevel = $this->gaCategory1stLevel;
$gaEventCategory = $this->gaEventCategory;

$this->gaObjs = array();
$this->jsonresultobject = [];
?>
<?php



$i = 0;
$listPos = ($paginator->getCurrentPageNumber()-1) * $paginator->getItemCountPerPage();
/** @var $resultSet Demi_Accommodation_Search_ResultSet_Accommodation */
foreach( $paginator as $resultSet ) {
    $time1 = millitime();
    $i++;
    $listPos++;
    if($isPackageSearch and !$resultSet instanceof Demi_Accommodation_Search_ResultSet_HousePackageMaster) {
        $product = Demi_AccommodationProduct::getById($resultSet->getProductId());
        /** @var Demi_AccommodationService $service */
        $service = $product->getParent();
        /** @var Demi_AccommodationServiceProvider $acco */
        $acco = $service->getParent();
    } else {
        $acco = Demi_AccommodationServiceProvider::getById($resultSet->getAccommodationId());
    }
    $addArray = array();
    if(! $_GET["randSeed"]) {
        $addArray["randSeed"] = $params->getOrderRandSeed();
    }

    $category2ndLevel = preg_replace("/\s+/", '', str_replace('/', ',', $acco->getCategoryNames(1, ",", 'de')));

    $addArray = array();
    if(! $_GET["randSeed"]) {
        $addArray["randSeed"] = $params->getOrderRandSeed();
    }
    if($noDate) {
        if($isPackageSearch and !$resultSet instanceof Demi_Accommodation_Search_ResultSet_HousePackageMaster) {
            $minPrice = $resultSet->getBasePrice();
            $housePackage = Demi_HousePackageMaster::getById($resultSet->getHousePackageMasterId());
            $detailUrl = Demi2015_Website_Helper::createUrlForPackage($acco, $housePackage,$_GET, $this, $this->language);
        } else if ($isPackageSearch) {
            $minPrice = $resultSet->getMinPriceBase();
            $housePackage = Demi_HousePackageMaster::getById($resultSet->getHousePackageMasterId());
            $detailUrl = Demi2015_Website_Helper::createUrlForPackage($acco, $housePackage,$_GET, $this, $this->language);
        } else {
            $minPrice = $resultSet->getMinPriceBase();
            $detailUrl = Demi2015_Website_Helper::createUrlForAcco($acco, array_merge($_GET + $addArray), $this, $this->language);
        }

    } else {
        if($isPackageSearch and  $resultSet instanceof Demi_Accommodation_Search_ResultSet_HousePackageMaster) {
            /** @var Demi_Accommodation_Search_ResultSet_HousePackageMaster $resultSet */
            $minPrice = $resultSet->getMinPrice();
            $housePackage = Demi_HousePackageMaster::getById($resultSet->getHousePackageMasterId());
            $detailUrl = Demi2015_Website_Helper::createUrlForPackage($acco, $housePackage,$_GET, $this, $this->language);
        } else if ($isPackageSearch) {
            $minPrice = $resultSet->getPrice();
            $addArray["hpId"] = $resultSet->getProductId();
            $detailUrl = Demi2015_Website_Helper::createUrlForAcco($acco, array_merge($_GET + $addArray), $this, $this->language);
        } else {
            /** @var Demi_Accommodation_Search_ResultSet_Accommodation $resultSet */
            $minPrice = $resultSet->getMinPrice();
            $minDate = $resultSet->getMinDateFrom($params->getDateFrom());
            $addArray["mp"] = $minPrice;
            if(!$minDate) {
                $minDate = clone $params->getDateFrom();
            }

            $addArray["minDate"] = $minDate->format("d.m.Y");
            $urlParams = array_merge($_GET + $addArray);
            if($params->getIsCorridor()) {
                $urlParams["priceFrom"] = null;
                $urlParams["priceTo"] = null;
            }
            $detailUrl = Demi2015_Website_Helper::createUrlForAcco($acco, $urlParams, $this, $this->language);
        }


    }

    if($isPackageSearch and $housePackage) {
        $cleanDetailUrl = Demi2015_Website_Helper::createUrlForPackage($acco, $housePackage,array(), $this, $this->language);
    } else {
        $cleanDetailUrl = Demi2015_Website_Helper::createUrlForAcco($acco, array(), $this, $this->language);
    }

    ?>


    <?php

    $hotelrowresult = getJsonResults($acco,$product,$gaImpressionObject,$isPackageSearch,$resultSet,$unitCategoryArray,$minPrice,$detailUrl,$cleanDetailUrl,$noDate,$i,$listPos,$this);

    $this->jsonresultobject[] = $hotelrowresult;

    ?>
<?php } ?>


<?php if($paginator->getTotalItemCount() < 1 && !$params->getIsAdditional()) { ?>
    <?php $this->jsonresultobject[] = null; ?>

<?php } ?>
<?php echo json_encode($this->jsonresultobject); exit(); ?>


